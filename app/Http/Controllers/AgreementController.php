<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAgreementRequest;
use App\Http\Requests\UploadSignedAgreementRequest;
use App\Models\Agreement;
use App\Models\AgreementVersion;
use App\Services\OrderDocumentService;
use Illuminate\Support\Facades\Storage;
/**
 * PKS dibuat otomatis dari order. Controller ini mengurus kirim, tanda tangan, dan unduh.
 */
class AgreementController extends Controller
{
    public function __construct(private OrderDocumentService $documents) {}

    public function index()
    {
        $agreements = Agreement::with('client')->latest()->paginate(15);
        return view('agreements.index', compact('agreements'));
    }
    public function show(Agreement $agreement)
    {
        $agreement->load('versions', 'client', 'order');
        return view('agreements.show', compact('agreement'));
    }
    /** Revisi draf memakai variabel yang sama dengan versi sebelumnya. */
    public function update(StoreAgreementRequest $request, Agreement $agreement)
    {
        abort_if($agreement->status === 'signed', 403, 'Perjanjian bertanda tangan tidak dapat diedit.');
        $template = $agreement->template ?: $this->documents->defaultTemplate('agreement');
        abort_unless($template, 404, 'Template perjanjian belum tersedia.');

        $agreement->update(['current_version' => $agreement->current_version + 1]);
        $this->documents->createVersion($agreement, $template, $request->validated()['variables'] ?? [], $agreement->current_version);

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
}
