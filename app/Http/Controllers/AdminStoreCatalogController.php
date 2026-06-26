<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\LegacyBook;
use App\Models\StoreCatalogItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminStoreCatalogController extends Controller
{
    public function index()
    {
        $books = Book::whereIn('workflow_status', ['isbn_approved', 'selesai'])
            ->orderBy('judul')
            ->get(['id', 'judul', 'subjudul', 'penulis_1', 'selling_price']);

        $legacyBooks = LegacyBook::orderBy('title')
            ->get(['id', 'title', 'subtitle', 'author_name', 'list_price']);

        $items = StoreCatalogItem::with(['book:id,judul', 'legacyBook:id,title'])
            ->orderByDesc('is_active')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(30);

        return view('finance.store.catalog', compact('books', 'legacyBooks', 'items'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'book_id' => $request->input('book_id') ?: null,
            'legacy_book_id' => $request->input('legacy_book_id') ?: null,
        ]);

        $data = $request->validate([
            'book_id' => ['nullable', 'exists:books,id'],
            'legacy_book_id' => ['nullable', 'exists:legacy_books,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'product_type' => ['required', 'in:print,ebook,print_ebook'],
            'description' => ['nullable', 'string'],
            'list_price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['nullable', 'numeric', 'min:0'],
            'ebook_price' => ['nullable', 'numeric', 'min:0'],
            'ebook_promo_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'ebook_pdf' => ['required_if:product_type,ebook,print_ebook', 'file', 'mimes:pdf', 'max:20480'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        if (!empty($data['book_id']) && !empty($data['legacy_book_id'])) {
            return back()->with('warning', 'Pilih salah satu sumber buku: naskah atau legacy.');
        }

        $slugBase = Str::slug($data['title']);
        $slug = $slugBase;
        $counter = 2;

        while (StoreCatalogItem::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image_file')) {
            $coverStored = $request->file('cover_image_file')->store('storefront/covers', 'public');
            $coverImagePath = Storage::url($coverStored);
        }

        $ebookReadLink = null;
        if ($request->hasFile('ebook_pdf')) {
            $ebookStored = $request->file('ebook_pdf')->store('storefront/ebooks', 'public');
            $ebookReadLink = Storage::url($ebookStored);
        }

        StoreCatalogItem::create([
            'book_id' => $data['book_id'] ?? null,
            'legacy_book_id' => $data['legacy_book_id'] ?? null,
            'slug' => $slug,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'author_name' => $data['author_name'] ?? null,
            'product_type' => $data['product_type'],
            'description' => $data['description'] ?? null,
            'list_price' => $data['list_price'],
            'promo_price' => $data['promo_price'] ?? null,
            'ebook_price' => (($data['product_type'] ?? '') === 'print_ebook') ? ($data['ebook_price'] ?? null) : null,
            'ebook_promo_price' => (($data['product_type'] ?? '') === 'print_ebook') ? ($data['ebook_promo_price'] ?? null) : null,
            'stock' => $data['stock'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'cover_image_path' => $coverImagePath,
            'ebook_read_link' => $ebookReadLink,
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return back()->with('success', 'Item katalog store berhasil ditambahkan.');
    }

    public function update(Request $request, StoreCatalogItem $item)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'product_type' => ['required', 'in:print,ebook,print_ebook'],
            'description' => ['nullable', 'string'],
            'list_price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['nullable', 'numeric', 'min:0'],
            'ebook_price' => ['nullable', 'numeric', 'min:0'],
            'ebook_promo_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'ebook_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
            'ebook_read_link' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        if (
            in_array((string) $data['product_type'], ['ebook', 'print_ebook'], true)
            && !$request->hasFile('ebook_pdf')
            && empty($item->ebook_read_link)
        ) {
            return back()
                ->withInput()
                ->with('warning', 'Upload PDF ebook wajib untuk tipe produk Ebook atau Print + Ebook.');
        }

        $coverImagePath = $data['cover_image_path'] ?? $item->cover_image_path;
        if ($request->hasFile('cover_image_file')) {
            $coverStored = $request->file('cover_image_file')->store('storefront/covers', 'public');
            $coverImagePath = Storage::url($coverStored);
        }

        $ebookReadLink = $data['ebook_read_link'] ?? $item->ebook_read_link;
        if ($request->hasFile('ebook_pdf')) {
            $ebookStored = $request->file('ebook_pdf')->store('storefront/ebooks', 'public');
            $ebookReadLink = Storage::url($ebookStored);
        }

        $item->update([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'author_name' => $data['author_name'] ?? null,
            'product_type' => $data['product_type'],
            'description' => $data['description'] ?? null,
            'list_price' => $data['list_price'],
            'promo_price' => $data['promo_price'] ?? null,
            'ebook_price' => (($data['product_type'] ?? '') === 'print_ebook') ? ($data['ebook_price'] ?? null) : null,
            'ebook_promo_price' => (($data['product_type'] ?? '') === 'print_ebook') ? ($data['ebook_promo_price'] ?? null) : null,
            'stock' => $data['stock'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'cover_image_path' => $coverImagePath,
            'ebook_read_link' => $ebookReadLink,
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return back()->with('success', 'Item katalog store berhasil diperbarui.');
    }
}
