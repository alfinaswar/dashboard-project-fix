<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreProjectRequest; use App\Models\ActivityLog; use App\Models\Client; use App\Models\Project; use Illuminate\Support\Facades\DB;
class ProjectController extends Controller
{
    public function index(){ $projects=Project::with(['client','stages'])->latest()->paginate(15); return view('projects.index',compact('projects')); }
    public function create(){return view('projects.form',['project'=>new Project(['status'=>'planning']),'clients'=>Client::orderBy('name')->get(),'defaultStages'=>[['name'=>'Analisis','weight'=>10],['name'=>'Desain','weight'=>15],['name'=>'Development','weight'=>35],['name'=>'Testing','weight'=>20],['name'=>'Deploy','weight'=>10],['name'=>'Maintenance','weight'=>10]]]);}
    public function store(StoreProjectRequest $request){$data=$request->validated();$stages=$data['stages'];unset($data['stages']);$project=DB::transaction(function()use($data,$stages){$project=Project::create($data);foreach($stages as $position=>$stage)$project->stages()->create([...$stage,'position'=>$position]);$this->log($project,'created',$data);return $project;});return redirect()->route('projects.show',$project)->with('success','Proyek berhasil ditambahkan.');}
    public function show(Project $project){$project->load(['client','stages']);return view('projects.show',compact('project'));}
    public function edit(Project $project){$project->load('stages');return view('projects.form',['project'=>$project,'clients'=>Client::orderBy('name')->get(),'defaultStages'=>$project->stages]);}
    public function update(StoreProjectRequest $request,Project $project){$data=$request->validated();$stages=$data['stages'];unset($data['stages']);DB::transaction(function()use($data,$stages,$project){$before=$project->only(['name','status','target_date']);$project->update($data);$project->stages()->delete();foreach($stages as $position=>$stage)$project->stages()->create([...$stage,'position'=>$position]);$this->log($project,'updated',['before'=>$before,'after'=>$data]);});return redirect()->route('projects.show',$project)->with('success','Proyek berhasil diperbarui.');}
    public function destroy(Project $project){$project->delete();$this->log($project,'deleted');return redirect()->route('projects.index')->with('success','Proyek berhasil dihapus.');}
    private function log(Project $project,string $action,array $changes=[]):void{ActivityLog::create(['user_id'=>auth()->id(),'subject_type'=>Project::class,'subject_id'=>$project->id,'action'=>$action,'changes'=>$changes,'created_at'=>now()]);}
}
