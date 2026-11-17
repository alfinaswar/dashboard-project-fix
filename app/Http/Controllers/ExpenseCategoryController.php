<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::orderBy('name')->paginate(15);
        return view('expense-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:expense_categories,name']], ['name.required' => 'Nama kategori wajib diisi.', 'name.unique' => 'Nama kategori sudah digunakan.']);
        ExpenseCategory::create($data);
        return back()->with('success', 'Kategori pengeluaran berhasil ditambahkan.');
    }

    public function update(Request $request, ExpenseCategory $expense_category)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:expense_categories,name,'.$expense_category->id]], ['name.required' => 'Nama kategori wajib diisi.']);
        $expense_category->update($data);
        return back()->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }

    public function destroy(ExpenseCategory $expense_category)
    {
        $expense_category->delete();
        return back()->with('success', 'Kategori pengeluaran berhasil dihapus.');
    }
}
