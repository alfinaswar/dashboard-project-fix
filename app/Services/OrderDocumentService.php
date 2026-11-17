<?php

namespace App\Services;

use App\Models\Agreement;
use App\Models\AgreementVersion;
use App\Models\DocumentTemplate;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

/**
 * Satu tempat untuk membuat invoice, PKS, dan menghitung sisa tagihan order.
 * Dipakai halaman detail order, halaman pembayaran, dan dashboard.
 */
class OrderDocumentService
{
    /** Total yang sudah dibayar klien untuk order ini (semua invoice). */
    public function paidFor(Order $order): int
    {
        return (int) $order->invoices()->with('payments')->get()
            ->flatMap(fn (Invoice $invoice) => $invoice->payments)
            ->sum('amount');
    }

    /** Sisa tagihan yang belum dibayar klien. Hanya invoice order, bukan invoice pembayaran. */
    public function outstandingFor(Order $order): int
    {
        return max(0, (int) $order->invoices()->whereNull('payment_id')->sum('total') - $this->paidFor($order));
    }

    /**
     * Buat invoice untuk order kalau belum ada. Nilai invoice = nilai kotor order.
     * Tanpa template default, invoice tetap dibuat tapi tanpa file PDF.
     */
    public function ensureInvoice(Order $order): Invoice
    {
        $existing = $order->invoices()->whereNull('payment_id')->latest('id')->first();

        if ($existing) {
            return $existing;
        }

        $issuedAt = Carbon::parse($order->ordered_at ?? now());
        $dueDays = (int) setting('invoice_due_days', 14);
        $number = $this->nextNumber('invoice_number_format', 'ORD/{YYYY}/{MM}/{NUMBER}', $issuedAt, fn (string $prefix) => Invoice::whereNull('payment_id')->where('number', 'like', $prefix.'%')->orderByDesc('number')->value('number'));
        $template = $this->defaultTemplate('invoice');

        $invoice = Invoice::create([
            'number' => $number,
            'source' => 'generated',
            'order_id' => $order->id,
            'template_id' => $template?->id,
            'issued_at' => $issuedAt->toDateString(),
            'due_date' => $issuedAt->copy()->addDays($dueDays)->toDateString(),
            'subtotal' => (int) $order->gross_amount,
            'tax_amount' => 0,
            'total' => (int) $order->gross_amount,
            'status' => 'unpaid',
        ]);

        // PDF selalu dibuat dari view invoice, tidak bergantung template.
        try {
            $path = 'invoices/'.str_replace('/', '-', $invoice->number).'.pdf';
            Storage::disk('private')->put($path, Pdf::loadView('invoices.pdf', compact('invoice'))->output());
            $invoice->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            Log::warning('Gagal membuat PDF invoice '.$invoice->number.': '.$e->getMessage());
        }

        return $invoice;
    }

    /**
     * Buat invoice untuk satu pembayaran masuk. Nomornya ikut nomor invoice order
     * dengan akhiran -B1, -B2, dst supaya mudah dilacak ke ordernya.
     */
    public function createPaymentInvoice(Order $order, Payment $payment): Invoice
    {
        $orderInvoice = $this->ensureInvoice($order);
        $issuedAt = Carbon::parse($payment->paid_at ?? now());
        $sequence = $orderInvoice->payments()->where('id', '<=', $payment->id)->count();

        $invoice = Invoice::create([
            'number' => $orderInvoice->number.'-B'.$sequence,
            'source' => 'generated',
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'template_id' => $orderInvoice->template_id,
            'issued_at' => $issuedAt->toDateString(),
            'due_date' => $issuedAt->toDateString(),
            'subtotal' => (int) $payment->amount,
            'tax_amount' => 0,
            'total' => (int) $payment->amount,
            'status' => 'paid',
        ]);

        try {
            $path = 'invoices/'.str_replace('/', '-', $invoice->number).'.pdf';
            Storage::disk('private')->put($path, Pdf::loadView('invoices.pdf', compact('invoice'))->output());
            $invoice->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            Log::warning('Gagal membuat PDF invoice pembayaran '.$invoice->number.': '.$e->getMessage());
        }

        return $invoice;
    }

    /**
     * Buat draft PKS untuk order kalau belum ada dan template perjanjian tersedia.
     */
    public function ensureAgreement(Order $order): ?Agreement
    {
        $existing = $order->agreements()->latest('id')->first();

        if ($existing) {
            return $existing;
        }

        $template = $this->defaultTemplate('agreement');

        if (! $template) {
            return null;
        }

        $date = Carbon::parse($order->ordered_at ?? now());
        $number = $this->nextNumber('agreement_number_format', 'PKS/{YYYY}/{NUMBER}', $date, fn (string $prefix) => Agreement::where('number', 'like', $prefix.'%')->orderByDesc('number')->value('number'));

        $agreement = Agreement::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'template_id' => $template->id,
            'number' => $number,
            'title' => 'Perjanjian Kerjasama - '.$order->title,
            'status' => 'draft',
            'current_version' => 1,
        ]);

        try {
            $this->createVersion($agreement, $template, $this->variablesFor($order, $number), 1);
        } catch (\Throwable $e) {
            Log::warning('Gagal membuat dokumen PKS '.$number.': '.$e->getMessage());
        }

        return $agreement;
    }

    /** Variabel template yang diisi otomatis dari data order dan pengaturan usaha. */
    public function variablesFor(Order $order, string $agreementNumber): array
    {
        $order->loadMissing('client', 'platform');

        return [
            'nomor_pks' => $agreementNumber,
            'nomor_order' => $order->external_order_ref ?: 'ORD-'.$order->id,
            'tanggal' => format_date($order->ordered_at ?? now(), 'd/m/Y'),
            'tanggal_mulai' => format_date($order->ordered_at ?? now(), 'd/m/Y'),
            'deadline' => $order->deadline ? format_date($order->deadline, 'd/m/Y') : '-',
            'judul_pekerjaan' => $order->title,
            'nilai_pekerjaan' => format_rupiah($order->gross_amount),
            'nama_klien' => $order->client?->name ?? '-',
            'alamat_klien' => $order->client?->address ?: '-',
            'platform' => $order->platform?->name ?? 'Langsung',
            'nama_perusahaan' => setting('company_name', ''),
            'alamat_perusahaan' => setting('company_address', ''),
            'nama_penandatangan' => setting('signee_name', ''),
            'nama_bank' => setting('bank_name', ''),
            'nomor_rekening' => setting('bank_account_number', ''),
            'atas_nama_rekening' => setting('bank_account_name', ''),
            'masa_revisi' => (string) setting('revision_days', 14),
        ];
    }

    public function createVersion(Agreement $agreement, DocumentTemplate $template, array $variables, int $version): void
    {
        $body = preg_replace_callback('/{{\s*([^}]+)\s*}}/', fn ($m) => e($variables[trim($m[1])] ?? '['.trim($m[1]).']'), $template->body);
        $versionModel = AgreementVersion::create(['agreement_id' => $agreement->id, 'version_number' => $version, 'variables' => $variables, 'body_rendered' => $body, 'created_at' => now()]);
        $versionModel->update(['pdf_path' => $this->pdf($versionModel), 'docx_path' => $this->docx($versionModel)]);
    }

    /** Template default untuk satu jenis dokumen; null kalau pengguna belum membuatnya. */
    public function defaultTemplate(string $type): ?DocumentTemplate
    {
        return DocumentTemplate::where('type', $type)->orderByDesc('is_default')->orderBy('id')->first();
    }

    /**
     * Nomor dokumen berurutan mengikuti format di pengaturan, mis. INV/{YYYY}/{MM}/{NUMBER}.
     */
    private function nextNumber(string $settingKey, string $fallback, Carbon $date, callable $lastNumber): string
    {
        $format = (string) setting($settingKey, $fallback);
        $prefix = rtrim(str_replace(['{YYYY}', '{MM}', '{NUMBER}'], [$date->format('Y'), $date->format('m'), ''], $format), '/').'/';
        $last = $lastNumber($prefix);

        return $prefix.str_pad((string) (($last ? (int) substr($last, -4) : 0) + 1), 4, '0', STR_PAD_LEFT);
    }

    private function pdf(AgreementVersion $version): string
    {
        $path = "agreements/{$version->agreement_id}/v{$version->version_number}.pdf";
        Storage::disk('private')->put($path, Pdf::loadHTML($version->body_rendered)->output());

        return $path;
    }

    private function docx(AgreementVersion $version): string
    {
        $word = new PhpWord();
        $section = $word->addSection();
        $section->addText(strip_tags($version->body_rendered));
        $path = "agreements/{$version->agreement_id}/v{$version->version_number}.docx";
        $tmp = tempnam(sys_get_temp_dir(), 'pks');
        IOFactory::createWriter($word, 'Word2007')->save($tmp);
        Storage::disk('private')->put($path, file_get_contents($tmp));
        @unlink($tmp);

        return $path;
    }
}
