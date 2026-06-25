<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\LegacyBook;
use App\Models\StoreCatalogItem;
use Illuminate\Http\Request;
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
        $data = $request->validate([
            'book_id' => ['nullable', 'required_without:legacy_book_id', 'exists:books,id'],
            'legacy_book_id' => ['nullable', 'required_without:book_id', 'exists:legacy_books,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'list_price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
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

        StoreCatalogItem::create([
            'book_id' => $data['book_id'] ?? null,
            'legacy_book_id' => $data['legacy_book_id'] ?? null,
            'slug' => $slug,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'author_name' => $data['author_name'] ?? null,
            'description' => $data['description'] ?? null,
            'list_price' => $data['list_price'],
            'promo_price' => $data['promo_price'] ?? null,
            'stock' => $data['stock'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'cover_image_path' => $data['cover_image_path'] ?? null,
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
            'description' => ['nullable', 'string'],
            'list_price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $item->update([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'author_name' => $data['author_name'] ?? null,
            'description' => $data['description'] ?? null,
            'list_price' => $data['list_price'],
            'promo_price' => $data['promo_price'] ?? null,
            'stock' => $data['stock'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'cover_image_path' => $data['cover_image_path'] ?? null,
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return back()->with('success', 'Item katalog store berhasil diperbarui.');
    }
}
