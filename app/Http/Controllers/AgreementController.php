<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAgreementRequest;
use App\Http\Requests\UploadSignedAgreementRequest;
use App\Models\Agreement;
use App\Models\AgreementVersion;
use App\Models\Client;
use App\Models\DocumentTemplate;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
class AgreementController extends Controller
{
    public function index()
    {
        $agreements = Agreement::with('client')->latest()->paginate(15);
        return view('agreements.index', compact('agreements'));
    }
    public function create()
    {
        return view('agreements.form', ['clients' => Client::orderBy('name')->get(), 'orders' => Order::with('client')->latest('ordered_at')->get(), 'templates' => DocumentTemplate::where('type', 'agreement')->orderBy('name')->get()]);
    }
    public function store(StoreAgreementRequest $request)
    {
        $data = $request->validated();
        $data['number'] = $this->nextNumber();
        $template = DocumentTemplate::findOrFail($data['template_id']);
        $variables = $data['variables'] ?? [];
        $agreement = Agreement::create($data);
        $this->createVersion($agreement, $template, $variables, 1);
        return redirect()->route('agreements.show', $agreement)->with('success', 'Draft perjanjian berhasil dibuat.');
    }
    public function show(Agreement $agreement)
    {
        $agreement->load('versions', 'client', 'order');
        return view('agreements.show', compact('agreement'));
    }
    public function update(StoreAgreementRequest $request, Agreement $agreement)
    {
        abort_if($agreement->status === 'signed', 403, 'Perjanjian bertanda tangan tidak dapat diedit.');
        $data = $request->validated();
        $template = DocumentTemplate::findOrFail($data['template_id']);
        $agreement->update(['client_id' => $data['client_id'], 'order_id' => $data['order_id'] ?? null, 'template_id' => $data['template_id'], 'title' => $data['title'], 'current_version' => $agreement->current_version + 1]);
        $this->createVersion($agreement, $template, $data['variables'] ?? [], $agreement->current_version);
        return back()->with('success', 'Versi perjanjian baru berhasil dibuat.');
    }
    public function send(Agreement $agreement)
    {
        abort_if($agreement->status === 'signed', 403);
        $agreement->update(['status' => 'sent']);
        return back()->with('success', 'Perjanjian ditandai sudah dikirim.');
    }
    public function sign(UploadSignedAgreementRequest $request, Agreement $agreement)
    {
        abort_if($agreement->status === 'signed', 403);
        $agreement->update(['status' => 'signed', 'signed_file_path' => $request->file('signed_file')->store('agreements/signed', 'private'), 'signed_at' => $request->date('signed_at')]);
        return back()->with('success', 'File perjanjian bertanda tangan berhasil disimpan.');
    }
    public function download(AgreementVersion $agreement_version, string $format)
    {
        $path = $format === 'pdf' ? $agreement_version->pdf_path : $agreement_version->docx_path;
        abort_unless($path && Storage::disk('private')->exists($path), 404);
        return Storage::disk('private')->download($path);
    }
    private function nextNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "PKS/{$year}/";
        $last = Agreement::where('number', 'like', $prefix . '%')->orderByDesc('number')->value('number');
        return $prefix . str_pad((string) (($last ? (int) substr($last, -4) : 0) + 1), 4, '0', STR_PAD_LEFT);
    }
    private function createVersion(Agreement $agreement, DocumentTemplate $template, array $variables, int $version): void
    {
        $body = preg_replace_callback('/{{\s*([^}]+)\s*}}/', fn($m) => e($variables[trim($m[1])] ?? '[' . trim($m[1]) . ']'), $template->body);
        $versionModel = AgreementVersion::create(['agreement_id' => $agreement->id, 'version_number' => $version, 'variables' => $variables, 'body_rendered' => $body, 'created_at' => now()]);
        $versionModel->update(['pdf_path' => $this->pdf($versionModel), 'docx_path' => $this->docx($versionModel)]);
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
