<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\RoleFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoleFileController extends Controller
{
    private const ALLOWED_ROLES = ['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'finance', 'superadmin'];

    public function index(Request $request)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);

        $activeRole = $user->role === 'superadmin'
            ? (string) $request->input('role', 'editor')
            : $user->role;

        if (!in_array($activeRole, self::ALLOWED_ROLES, true)) {
            $activeRole = 'editor';
        }

        $files = RoleFile::with(['uploader', 'book'])
            ->where('role', $activeRole)
            ->latest()
            ->paginate(24);

        $gallery = RoleFile::with('book')
            ->where('role', $activeRole)
            ->where('is_image', true)
            ->latest()
            ->limit(24)
            ->get();

        $booksQuery = Book::query()->orderBy('judul');
        if ($user->role === 'author') {
            $booksQuery->where('author_user_id', $user->id);
        }

        $books = $booksQuery->limit(300)->get(['id', 'judul', 'nomor_naskah']);

        return view('role-files.index', [
            'files' => $files,
            'gallery' => $gallery,
            'books' => $books,
            'activeRole' => $activeRole,
            'availableRoles' => self::ALLOWED_ROLES,
            'isSuperadmin' => $user->role === 'superadmin',
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);

        $data = $request->validate([
            'category' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'book_id' => ['nullable', 'exists:books,id'],
            'file' => ['required', 'file', 'max:30720'],
        ]);

        $file = $request->file('file');
        $role = $user->role;
        $mime = (string) $file->getMimeType();

        $path = $file->store('role-files/' . $role . '/' . now()->format('Y/m'), 'public');

        RoleFile::create([
            'user_id' => $user->id,
            'book_id' => $data['book_id'] ?: null,
            'role' => $role,
            'category' => $data['category'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'disk' => 'public',
            'file_path' => $path,
            'mime_type' => $mime,
            'file_size' => (int) $file->getSize(),
            'is_image' => str_starts_with($mime, 'image/'),
        ]);

        return back()->with('success', 'File role berhasil diunggah. Link file dapat disalin dari daftar.');
    }

    public function preview(RoleFile $roleFile)
    {
        $this->authorizeRoleFileAccess($roleFile);

        if (!Storage::disk($roleFile->disk)->exists($roleFile->file_path)) {
            abort(404);
        }

        $absolutePath = Storage::disk($roleFile->disk)->path($roleFile->file_path);

        return response()->file($absolutePath, [
            'Content-Type' => $roleFile->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($roleFile->file_path) . '"',
        ]);
    }

    public function download(RoleFile $roleFile)
    {
        $this->authorizeRoleFileAccess($roleFile);

        if (!Storage::disk($roleFile->disk)->exists($roleFile->file_path)) {
            abort(404);
        }

        $absolutePath = Storage::disk($roleFile->disk)->path($roleFile->file_path);

        return response()->download($absolutePath, $roleFile->title);
    }

    public function destroy(RoleFile $roleFile)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);
        $this->authorizeRoleFileAccess($roleFile);

        if ($user->role !== 'superadmin' && (int) $roleFile->user_id !== (int) $user->id) {
            return back()->with('warning', 'Hanya pengunggah atau superadmin yang dapat menghapus file.');
        }

        if (Storage::disk($roleFile->disk)->exists($roleFile->file_path)) {
            Storage::disk($roleFile->disk)->delete($roleFile->file_path);
        }

        $roleFile->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }

    public function share(Request $request, RoleFile $roleFile)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);
        $this->authorizeRoleFileAccess($roleFile);

        $days = (int) $request->input('expires_days', 7);
        $days = max(1, min(30, $days));

        $roleFile->update([
            'share_token' => (string) Str::uuid(),
            'share_expires_at' => now()->addDays($days),
        ]);

        return back()->with('success', 'Link share publik berhasil dibuat. Berlaku ' . $days . ' hari.');
    }

    public function shared(string $token)
    {
        $roleFile = RoleFile::where('share_token', $token)->firstOrFail();

        if (!$roleFile->share_expires_at || now()->greaterThan($roleFile->share_expires_at)) {
            abort(410, 'Link share sudah kedaluwarsa.');
        }

        if (!Storage::disk($roleFile->disk)->exists($roleFile->file_path)) {
            abort(404);
        }

        $absolutePath = Storage::disk($roleFile->disk)->path($roleFile->file_path);

        return response()->file($absolutePath, [
            'Content-Type' => $roleFile->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($roleFile->file_path) . '"',
        ]);
    }

    private function authorizeRoleFileAccess(RoleFile $roleFile): void
    {
        $user = auth()->user();

        if ($user->role === 'superadmin') {
            return;
        }

        if ($roleFile->role !== $user->role) {
            abort(403);
        }
    }

    private function ensureRoleAllowed(string $role): void
    {
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            abort(403);
        }
    }
}
