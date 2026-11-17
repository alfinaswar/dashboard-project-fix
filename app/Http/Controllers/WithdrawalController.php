<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreWithdrawalRequest; use App\Models\Order; use App\Models\Platform; use App\Models\Withdrawal; use Illuminate\Support\Facades\DB;
class WithdrawalController extends Controller
{
    public function index() { $withdrawals=Withdrawal::with('platform')->latest('withdrawn_at')->paginate(15); return view('withdrawals.index',compact('withdrawals')); }
    public function create() { $orders=Order::with('client')->where('payout_status','in_balance')->orderBy('ordered_at')->get(); return view('withdrawals.form',['platforms'=>Platform::where('is_active',true)->orderBy('name')->get(),'orders'=>$orders]); }
    public function store(StoreWithdrawalRequest $request) { $data=$request->validated(); $orders=Order::whereIn('id',$data['order_ids'])->where('payout_status','in_balance')->where('platform_id',$data['platform_id'])->get(); if($orders->count()!==count($data['order_ids'])) return back()->withInput()->with('error','Semua order harus berstatus saldo dan berasal dari platform yang sama.'); unset($data['order_ids']); DB::transaction(function() use($data,$orders,&$withdrawal){ $withdrawal=Withdrawal::create($data); $withdrawal->orders()->attach($orders->modelKeys()); $orders->each->update(['payout_status'=>'withdrawn']); }); return redirect()->route('withdrawals.index')->with('success','Penarikan berhasil dicatat.'); }
}
