<?php

namespace App\Http\Controllers;

use App\Models\LegacyBook;
use App\Models\User;
use Illuminate\Http\Request;

class AdminLegacyBookController extends Controller
{
    public function index()
    {
        $books = LegacyBook::with('author')
            ->latest('id')
            ->paginate(25);

        $authors = User::where('role', 'author')->orderBy('name')->get(['id', 'name']);

        return view('finance.legacy-books.index', compact('books', 'authors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'author_name' => ['required', 'string', 'max:255'],
            'author_user_id' => ['nullable', 'exists:users,id'],
            'isbn' => ['nullable', 'string', 'max:60'],
            'published_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'list_price' => ['nullable', 'numeric', 'min:0'],
            'royalty_enabled' => ['nullable', 'boolean'],
            'royalty_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'distribution_online' => ['nullable', 'boolean'],
            'distribution_ebook' => ['nullable', 'boolean'],
            'distribution_marketplace' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,inactive,archived'],
            'notes' => ['nullable', 'string'],
        ]);

        LegacyBook::create([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'author_name' => $data['author_name'],
            'author_user_id' => $data['author_user_id'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'published_year' => $data['published_year'] ?? null,
            'list_price' => $data['list_price'] ?? 0,
            'royalty_enabled' => (bool) ($data['royalty_enabled'] ?? false),
            'royalty_rate' => $data['royalty_rate'] ?? null,
            'distribution_online' => (bool) ($data['distribution_online'] ?? false),
            'distribution_ebook' => (bool) ($data['distribution_ebook'] ?? false),
            'distribution_marketplace' => (bool) ($data['distribution_marketplace'] ?? false),
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Buku katalog legacy berhasil ditambahkan.');
    }

    public function update(Request $request, LegacyBook $legacyBook)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'author_name' => ['required', 'string', 'max:255'],
            'author_user_id' => ['nullable', 'exists:users,id'],
            'isbn' => ['nullable', 'string', 'max:60'],
            'published_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'list_price' => ['nullable', 'numeric', 'min:0'],
            'royalty_enabled' => ['nullable', 'boolean'],
            'royalty_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'distribution_online' => ['nullable', 'boolean'],
            'distribution_ebook' => ['nullable', 'boolean'],
            'distribution_marketplace' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,inactive,archived'],
            'notes' => ['nullable', 'string'],
        ]);

        $legacyBook->update([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'author_name' => $data['author_name'],
            'author_user_id' => $data['author_user_id'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'published_year' => $data['published_year'] ?? null,
            'list_price' => $data['list_price'] ?? 0,
            'royalty_enabled' => (bool) ($data['royalty_enabled'] ?? false),
            'royalty_rate' => $data['royalty_rate'] ?? null,
            'distribution_online' => (bool) ($data['distribution_online'] ?? false),
            'distribution_ebook' => (bool) ($data['distribution_ebook'] ?? false),
            'distribution_marketplace' => (bool) ($data['distribution_marketplace'] ?? false),
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Buku katalog legacy berhasil diperbarui.');
    }
}
