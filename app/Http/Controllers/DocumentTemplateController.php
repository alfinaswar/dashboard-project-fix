<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreDocumentTemplateRequest; use App\Models\DocumentTemplate;
class DocumentTemplateController extends Controller
{
    public function index() { $templates=DocumentTemplate::orderBy('type')->orderBy('name')->paginate(15); return view('document-templates.index',compact('templates')); }
    public function create() { return view('document-templates.form',['template'=>new DocumentTemplate(['type'=>'invoice'])]); }
    public function store(StoreDocumentTemplateRequest $request) { $data=$request->validated(); if(!empty($data['is_default'])) DocumentTemplate::where('type',$data['type'])->update(['is_default'=>false]); DocumentTemplate::create($data); return redirect()->route('document-templates.index')->with('success','Template berhasil ditambahkan.'); }
    public function edit(DocumentTemplate $document_template) { return view('document-templates.form',['template'=>$document_template]); }
    public function update(StoreDocumentTemplateRequest $request, DocumentTemplate $document_template) { $data=$request->validated(); if(!empty($data['is_default'])) DocumentTemplate::where('type',$data['type'])->whereKeyNot($document_template->id)->update(['is_default'=>false]); $document_template->update($data); return redirect()->route('document-templates.index')->with('success','Template berhasil diperbarui.'); }
    public function destroy(DocumentTemplate $document_template) { $document_template->delete(); return back()->with('success','Template berhasil dihapus.'); }
}
