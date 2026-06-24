<?php

namespace App\Http\Controllers;

use App\Models\PrintPriceRule;
use Illuminate\Http\Request;

class AdminPrintPriceController extends Controller
{
    public function index()
    {
        $rules = PrintPriceRule::latest()->get();

        return view('admin.print-prices.index', compact('rules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'paper_type' => ['required', 'string', 'max:100'],
            'paper_size' => ['nullable', 'string', 'max:50'],
            'print_type' => ['required', 'string', 'max:50'],
            'min_pages' => ['required', 'integer', 'min:1'],
            'max_pages' => ['nullable', 'integer', 'gte:min_pages'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'price_per_page' => ['required', 'numeric', 'min:0'],
            'weight_per_copy_gram' => ['required', 'integer', 'min:50'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        PrintPriceRule::create($data);

        return back()->with('success', 'Harga cetak berhasil ditambahkan.');
    }

    public function update(Request $request, PrintPriceRule $printPrice)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'paper_type' => ['required', 'string', 'max:100'],
            'paper_size' => ['nullable', 'string', 'max:50'],
            'print_type' => ['required', 'string', 'max:50'],
            'min_pages' => ['required', 'integer', 'min:1'],
            'max_pages' => ['nullable', 'integer', 'gte:min_pages'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'price_per_page' => ['required', 'numeric', 'min:0'],
            'weight_per_copy_gram' => ['required', 'integer', 'min:50'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active', false);

        $printPrice->update($data);

        return back()->with('success', 'Harga cetak berhasil diperbarui.');
    }

    public function destroy(PrintPriceRule $printPrice)
    {
        $printPrice->delete();

        return back()->with('success', 'Harga cetak berhasil dihapus.');
    }
}
