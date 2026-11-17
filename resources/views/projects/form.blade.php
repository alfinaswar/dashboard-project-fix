@extends('layouts.admin')
@section('title', $project->exists ? 'Ubah Proyek' : 'Tambah Proyek')
@section('page_title', $project->exists ? 'Ubah Proyek' : 'Tambah Proyek')
@section('content')
<form method="POST" action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}">
    @csrf
    @if($project->exists) @method('PUT') @endif
    <div class="card card-primary"><div class="card-body">
        <div class="form-group"><label>Klien *</label><select name="client_id" class="form-control" required><option value="">Pilih klien</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected(old('client_id', $project->client_id) == $client->id)>{{ $client->name }}</option>@endforeach</select></div>
        <div class="form-group"><label>Nama Proyek *</label><input placeholder="Masukkan name" name="name" class="form-control" value="{{ old('name', $project->name) }}" required></div>
        <div class="row"><div class="col-md-4"><label>Tanggal Mulai</label><input placeholder="Pilih tanggal" type="date" name="start_date" class="form-control" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"></div><div class="col-md-4"><label>Target Selesai</label><input placeholder="Pilih tanggal" type="date" name="target_date" class="form-control" value="{{ old('target_date', $project->target_date?->format('Y-m-d')) }}"></div><div class="col-md-4"><label>Status *</label><select name="status" class="form-control">@foreach(['planning','running','done','on_hold'] as $status)<option value="{{ $status }}" @selected(old('status', $project->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div></div>
        <div class="form-group mt-3"><label>Deskripsi</label><textarea placeholder="Masukkan description" name="description" class="form-control">{{ old('description', $project->description) }}</textarea></div>
        <hr><h5>Tahapan</h5>
        @foreach($defaultStages as $i => $stage)
            @php($stage = is_object($stage) ? $stage : (object) $stage)
            <div class="row mb-2"><div class="col-md-4"><input placeholder="Masukkan stages[{{ $i }}][name]" name="stages[{{ $i }}][name]" class="form-control" value="{{ old("stages.$i.name", $stage->name) }}" required></div><div class="col-md-2"><input placeholder="Masukkan stages[{{ $i }}][weight]" type="number" min="0" max="100" name="stages[{{ $i }}][weight]" class="form-control" value="{{ old("stages.$i.weight", $stage->weight ?? 0) }}" required></div><div class="col-md-2"><input placeholder="Masukkan stages[{{ $i }}][progress percent]" type="number" min="0" max="100" name="stages[{{ $i }}][progress_percent]" class="form-control" value="{{ old("stages.$i.progress_percent", $stage->progress_percent ?? 0) }}" required></div><div class="col-md-2"><select name="stages[{{ $i }}][status]" class="form-control"><option value="todo">Todo</option><option value="doing">Doing</option><option value="done">Done</option></select></div><div class="col-md-2"><input placeholder="Pilih tanggal" type="date" name="stages[{{ $i }}][target_date]" class="form-control" value="{{ old("stages.$i.target_date", isset($stage->target_date) ? $stage->target_date?->format('Y-m-d') : '') }}"></div></div>
        @endforeach
    </div><div class="card-footer"><a href="{{ route('projects.index') }}" class="btn btn-secondary">Batal</a> <button class="btn btn-primary">Simpan</button></div></div>
</form>
@endsection
