<?php

namespace App\Http\Controllers;

use App\Models\StoreVoucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminStoreVoucherController extends Controller
{
    public function index()
    {
        $vouchers = StoreVoucher::orderByDesc('is_active')
            ->orderBy('code')
            ->latest('id')
            ->paginate(20);

        return view('finance.store.vouchers', compact('vouchers'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $data['code'] = strtoupper(trim((string) $data['code']));
        $data['used_count'] = 0;

        StoreVoucher::create($data);

        return back()->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function update(Request $request, StoreVoucher $voucher)
    {
        $data = $this->validatePayload($request, $voucher->id);
        $data['code'] = strtoupper(trim((string) $data['code']));

        $voucher->update($data);

        return back()->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(StoreVoucher $voucher)
    {
        $voucher->delete();

        return back()->with('success', 'Voucher berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('store_vouchers', 'code')->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_subtotal' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'applies_to' => ['required', 'in:all,print,ebook,print_ebook'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
