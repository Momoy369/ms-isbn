<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookFile;
use App\Models\RoleFile;
use App\Services\FinalBookPackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoleFileController extends Controller
{
    private const ALLOWED_ROLES = ['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'finance', 'superadmin', 'author'];
    private const ACCESS_SCOPES = ['private', 'role', 'all_roles', 'public'];

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

        $search = trim((string) $request->input('q', ''));
        $folder = trim((string) $request->input('folder', ''));
        $viewMode = (string) $request->input('view', 'table');
        if (!in_array($viewMode, ['table', 'grid'], true)) {
            $viewMode = 'table';
        }

        $baseQuery = RoleFile::with(['uploader', 'book']);

        $roleFilter = trim((string) $request->input('role_filter', ''));
        if ($roleFilter !== '' && in_array($roleFilter, self::ALLOWED_ROLES, true)) {
            $baseQuery->where('role', $roleFilter);
        }

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('file_path', 'like', '%' . $search . '%')
                    ->orWhereHas('book', function ($bookQuery) use ($search) {
                        $bookQuery->where('judul', 'like', '%' . $search . '%')
                            ->orWhere('nomor_naskah', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($folder !== '') {
            $baseQuery->where('file_path', 'like', $folder . '/%');
        }

        $files = (clone $baseQuery)
            ->latest()
            ->paginate(24)
            ->withQueryString();

        $files->getCollection()->transform(function (RoleFile $item) use ($user) {
            $item->can_access = $this->canAccessRoleFile($item, $user->role, (int) $user->id);
            $item->access_label = $this->scopeLabel((string) ($item->access_scope ?? 'role'));
            $item->allowed_roles_csv = implode(',', (array) ($item->allowed_roles ?? []));
            $item->allowed_emails_csv = implode(',', (array) ($item->allowed_emails ?? []));
            $item->allowed_domains_csv = implode(',', (array) ($item->allowed_domains ?? []));
            return $item;
        });

        $gallery = (clone $baseQuery)
            ->where('is_image', true)
            ->latest()
            ->limit(24)
            ->get()
            ->filter(fn(RoleFile $item) => $this->canAccessRoleFile($item, $user->role, (int) $user->id))
            ->values();

        $allPaths = (clone $baseQuery)
            ->select('file_path')
            ->limit(500)
            ->get()
            ->pluck('file_path')
            ->filter();

        $folders = $allPaths
            ->map(fn($path) => trim((string) dirname((string) $path), '.'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'linked_books' => (clone $baseQuery)->whereNotNull('book_id')->count(),
            'images' => (clone $baseQuery)->where('is_image', true)->count(),
            'folders' => $folders->count(),
        ];

        $logAction = trim((string) $request->input('log_action', ''));
        $logResult = trim((string) $request->input('log_result', ''));
        $logFileId = (int) $request->input('log_file_id', 0);

        $selectedLogFile = null;
        if ($logFileId > 0) {
            $selectedLogFile = RoleFile::query()
                ->select(['id', 'title', 'role'])
                ->find($logFileId);
        }

        $accessLogsQuery = DB::table('role_file_access_logs as logs')
            ->leftJoin('role_files as rf', 'rf.id', '=', 'logs.role_file_id')
            ->leftJoin('users as u', 'u.id', '=', 'logs.user_id')
            ->select([
                'logs.id',
                'logs.role_file_id',
                'logs.email',
                'logs.role',
                'logs.action',
                'logs.granted',
                'logs.scope',
                'logs.note',
                'logs.ip_address',
                'logs.created_at',
                'rf.title as file_title',
                'rf.role as file_role',
                'u.name as user_name',
            ]);

        if ($roleFilter !== '' && in_array($roleFilter, self::ALLOWED_ROLES, true)) {
            $accessLogsQuery->where('rf.role', $roleFilter);
        }

        if ($search !== '') {
            $accessLogsQuery->where(function ($query) use ($search) {
                $query->where('rf.title', 'like', '%' . $search . '%')
                    ->orWhere('logs.email', 'like', '%' . $search . '%')
                    ->orWhere('logs.ip_address', 'like', '%' . $search . '%')
                    ->orWhere('logs.note', 'like', '%' . $search . '%');
            });
        }

        if ($logAction !== '') {
            $accessLogsQuery->where('logs.action', $logAction);
        }

        if ($logFileId > 0) {
            $accessLogsQuery->where('logs.role_file_id', $logFileId);
        }

        if ($logResult === 'granted') {
            $accessLogsQuery->where('logs.granted', true);
        } elseif ($logResult === 'denied') {
            $accessLogsQuery->where('logs.granted', false);
        }

        $accessLogs = (clone $accessLogsQuery)
            ->orderByDesc('logs.id')
            ->limit(120)
            ->get();

        $logSummary = [
            'total' => (clone $accessLogsQuery)->count(),
            'granted' => (clone $accessLogsQuery)->where('logs.granted', true)->count(),
            'denied' => (clone $accessLogsQuery)->where('logs.granted', false)->count(),
        ];

        $logActions = DB::table('role_file_access_logs')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $booksQuery = Book::query()->orderBy('judul');
        if ($user->role === 'author') {
            $booksQuery->where('author_user_id', $user->id);
        }

        $books = $booksQuery->limit(300)->get(['id', 'judul', 'nomor_naskah']);

        return view('role-files.index', [
            'files' => $files,
            'gallery' => $gallery,
            'books' => $books,
            'stats' => $stats,
            'folders' => $folders,
            'search' => $search,
            'selectedFolder' => $folder,
            'viewMode' => $viewMode,
            'roleFilter' => $roleFilter,
            'accessLogs' => $accessLogs,
            'logSummary' => $logSummary,
            'logActions' => $logActions,
            'logAction' => $logAction,
            'logResult' => $logResult,
            'logFileId' => $logFileId,
            'selectedLogFile' => $selectedLogFile,
            'accessScopes' => self::ACCESS_SCOPES,
            'activeRole' => $activeRole,
            'availableRoles' => self::ALLOWED_ROLES,
            'isSuperadmin' => $user->role === 'superadmin',
        ]);
    }

    public function store(Request $request, FinalBookPackageService $finalPackage)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);

        $data = $request->validate([
            'role' => ['nullable', 'string', 'max:30'],
            'category' => ['required', 'string', 'max:50'],
            'book_file_type' => ['nullable', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'book_id' => ['nullable', 'exists:books,id'],
            'access_scope' => ['nullable', 'in:private,role,all_roles,public'],
            'file' => ['nullable', 'file', 'max:30720'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:30720'],
        ]);

        $incomingFiles = [];
        if ($request->hasFile('file')) {
            $incomingFiles[] = $request->file('file');
        }
        if ($request->hasFile('files')) {
            foreach ((array) $request->file('files') as $upload) {
                if ($upload) {
                    $incomingFiles[] = $upload;
                }
            }
        }

        if (empty($incomingFiles)) {
            return back()->with('warning', 'Pilih minimal satu file untuk diunggah.')->withInput();
        }

        $role = $this->resolveTargetRole($user->role, (string) ($data['role'] ?? ''));
        $book = null;
        $accessScope = (string) ($data['access_scope'] ?? 'role');
        if (!in_array($accessScope, self::ACCESS_SCOPES, true)) {
            $accessScope = 'role';
        }

        if (!empty($data['book_id'])) {
            $book = Book::with('author')->find($data['book_id']);
        }

        $storageDir = $this->resolveStorageDirectory($role, $book);

        $totalUploaded = 0;
        $totalSynced = 0;

        foreach ($incomingFiles as $index => $file) {
            $mime = (string) $file->getMimeType();
            $path = $file->store($storageDir, 'public');
            $title = trim((string) ($data['title'] ?? ''));

            if ($title === '') {
                $title = (string) $file->getClientOriginalName();
            } elseif (count($incomingFiles) > 1) {
                $title .= ' #' . ($index + 1);
            }

            $roleFile = RoleFile::create([
                'user_id' => $user->id,
                'book_id' => $data['book_id'] ?: null,
                'role' => $role,
                'category' => $data['category'],
                'title' => $title,
                'description' => $data['description'] ?? null,
                'disk' => 'public',
                'file_path' => $path,
                'mime_type' => $mime,
                'file_size' => (int) $file->getSize(),
                'is_image' => str_starts_with($mime, 'image/'),
                'access_scope' => $accessScope,
                'allowed_roles' => null,
                'allowed_emails' => null,
                'allowed_domains' => null,
                'share_token' => $accessScope === 'public' ? ((string) Str::uuid()) : null,
                'share_expires_at' => null,
            ]);

            if ($book) {
                $candidateType = $this->resolveBookFileType(
                    (string) ($data['book_file_type'] ?? ''),
                    (string) $data['category'],
                    (string) $title
                );

                if ($candidateType !== null) {
                    $this->syncBookFileFromRoleUpload($book, $roleFile, $candidateType, $role);
                    if (in_array($candidateType, $finalPackage->requiredTypes(), true)) {
                        $finalPackage->syncDeliveryLink($book);
                    }
                    $totalSynced++;
                }
            }

            $totalUploaded++;
        }

        $message = $totalUploaded . ' file berhasil diunggah ke folder: ' . $storageDir;
        if ($totalSynced > 0) {
            $message .= ' | ' . $totalSynced . ' file tersinkron ke berkas buku.';
        }

        return back()->with('success', $message);
    }

    public function preview(RoleFile $roleFile)
    {
        $this->authorizeRoleFileAccess($roleFile, 'preview');

        if (!Storage::disk($roleFile->disk)->exists($roleFile->file_path)) {
            $this->logFileAccess($roleFile, 'preview', false, 'file_not_found');
            abort(404);
        }

        $absolutePath = Storage::disk($roleFile->disk)->path($roleFile->file_path);

        $this->logFileAccess($roleFile, 'preview', true, 'ok');

        return response()->file($absolutePath, [
            'Content-Type' => $roleFile->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($roleFile->file_path) . '"',
        ]);
    }

    public function download(RoleFile $roleFile)
    {
        $this->authorizeRoleFileAccess($roleFile, 'download');

        if (!Storage::disk($roleFile->disk)->exists($roleFile->file_path)) {
            $this->logFileAccess($roleFile, 'download', false, 'file_not_found');
            abort(404);
        }

        $absolutePath = Storage::disk($roleFile->disk)->path($roleFile->file_path);

        $this->logFileAccess($roleFile, 'download', true, 'ok');

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

    public function updateAccess(Request $request, RoleFile $roleFile)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);
        $this->authorizeRoleFileAccess($roleFile);
        $this->ensureCanModify($user->role, $user->id, $roleFile);

        $data = $request->validate([
            'access_scope' => ['required', 'in:private,role,all_roles,public'],
            'expires_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string', 'max:32'],
            'allowed_emails' => ['nullable', 'string', 'max:3000'],
            'allowed_domains' => ['nullable', 'string', 'max:3000'],
        ]);

        $scope = (string) $data['access_scope'];
        $days = (int) ($data['expires_days'] ?? 0);

        $payload = [
            'access_scope' => $scope,
            'allowed_roles' => $this->sanitizeRoles((array) ($data['allowed_roles'] ?? [])),
            'allowed_emails' => $this->sanitizeCsvList((string) ($data['allowed_emails'] ?? ''), true),
            'allowed_domains' => $this->sanitizeCsvList((string) ($data['allowed_domains'] ?? ''), false),
        ];

        if ($scope === 'public') {
            $payload['share_token'] = $roleFile->share_token ?: (string) Str::uuid();
            $payload['share_expires_at'] = $days > 0 ? now()->addDays($days) : null;
        } else {
            $payload['share_token'] = null;
            $payload['share_expires_at'] = null;
        }

        $roleFile->update($payload);

        return back()->with('success', 'Akses file diubah ke mode: ' . $this->scopeLabel($scope));
    }

    public function rename(Request $request, RoleFile $roleFile)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);
        $this->authorizeRoleFileAccess($roleFile);
        $this->ensureCanModify($user->role, $user->id, $roleFile);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $oldTitle = (string) $roleFile->title;
        $roleFile->update(['title' => $data['title']]);

        $bookFileQuery = BookFile::query()
            ->where('file_path', $roleFile->file_path);

        if ($roleFile->book_id) {
            $bookFileQuery->where('book_id', $roleFile->book_id);
        }

        $bookFileQuery->update([
            'original_name' => $data['title'],
        ]);

        return back()->with('success', 'Judul file berhasil diubah dari "' . $oldTitle . '" menjadi "' . $data['title'] . '".');
    }

    public function move(Request $request, RoleFile $roleFile)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);
        $this->authorizeRoleFileAccess($roleFile);
        $this->ensureCanModify($user->role, $user->id, $roleFile);

        $data = $request->validate([
            'target_folder' => ['required', 'string', 'max:255'],
        ]);

        $oldPath = (string) $roleFile->file_path;
        $targetFolder = trim(str_replace('\\', '/', $data['target_folder']), '/');

        if ($targetFolder === '' || str_contains($targetFolder, '..')) {
            return back()->with('warning', 'Target folder tidak valid.');
        }

        if (!str_starts_with($targetFolder, 'role-files/' . $roleFile->role . '/')) {
            return back()->with('warning', 'Folder tujuan harus tetap dalam ruang role ' . strtoupper($roleFile->role) . '.');
        }

        if (!Storage::disk($roleFile->disk)->exists($oldPath)) {
            return back()->with('warning', 'File fisik tidak ditemukan. Pindah folder dibatalkan.');
        }

        $filename = basename($oldPath);
        $newPath = $targetFolder . '/' . $filename;

        if ($newPath === $oldPath) {
            return back()->with('info', 'File sudah berada di folder yang dipilih.');
        }

        if (Storage::disk($roleFile->disk)->exists($newPath)) {
            $newPath = $targetFolder . '/' . pathinfo($filename, PATHINFO_FILENAME) . '-' . time() . '.' . pathinfo($filename, PATHINFO_EXTENSION);
        }

        Storage::disk($roleFile->disk)->move($oldPath, $newPath);
        $roleFile->update(['file_path' => $newPath]);

        $bookFileQuery = BookFile::query()
            ->where('file_path', $oldPath);

        if ($roleFile->book_id) {
            $bookFileQuery->where('book_id', $roleFile->book_id);
        }

        $bookFileQuery->update([
            'file_path' => $newPath,
        ]);

        return back()->with('success', 'File berhasil dipindah ke folder: ' . $targetFolder);
    }

    public function share(Request $request, RoleFile $roleFile)
    {
        $user = auth()->user();
        $this->ensureRoleAllowed($user->role);
        $this->authorizeRoleFileAccess($roleFile);

        $days = (int) $request->input('expires_days', 7);
        $days = max(1, min(30, $days));

        $roleFile->update([
            'access_scope' => 'public',
            'share_token' => (string) Str::uuid(),
            'share_expires_at' => now()->addDays($days),
        ]);

        return back()->with('success', 'Link share publik berhasil dibuat. Berlaku ' . $days . ' hari.');
    }

    public function shared(string $token)
    {
        $roleFile = RoleFile::where('share_token', $token)->firstOrFail();

        if (($roleFile->access_scope ?? 'role') !== 'public') {
            $this->logFileAccess($roleFile, 'shared-preview', false, 'scope_not_public');
            abort(403, 'Link share publik tidak aktif untuk file ini.');
        }

        if ($roleFile->share_expires_at && now()->greaterThan($roleFile->share_expires_at)) {
            $this->logFileAccess($roleFile, 'shared-preview', false, 'expired');
            abort(410, 'Link share sudah kedaluwarsa.');
        }

        if (!Storage::disk($roleFile->disk)->exists($roleFile->file_path)) {
            $this->logFileAccess($roleFile, 'shared-preview', false, 'file_not_found');
            abort(404);
        }

        $absolutePath = Storage::disk($roleFile->disk)->path($roleFile->file_path);

        $this->logFileAccess($roleFile, 'shared-preview', true, 'ok');

        return response()->file($absolutePath, [
            'Content-Type' => $roleFile->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($roleFile->file_path) . '"',
        ]);
    }

    private function authorizeRoleFileAccess(RoleFile $roleFile, string $action = 'access'): void
    {
        $user = auth()->user();
        if (!$this->canAccessRoleFile($roleFile, $user->role, (int) $user->id)) {
            $this->logFileAccess($roleFile, $action, false, 'forbidden_by_scope');
            abort(403, 'Folder ini bersifat private untuk role/owner tertentu.');
        }
    }

    private function ensureRoleAllowed(string $role): void
    {
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            abort(403);
        }
    }

    private function ensureCanModify(string $userRole, int $userId, RoleFile $roleFile): void
    {
        if ($userRole === 'superadmin') {
            return;
        }

        if ((int) $roleFile->user_id !== $userId) {
            abort(403, 'Hanya pengunggah atau superadmin yang dapat mengubah file.');
        }
    }

    private function resolveTargetRole(string $currentRole, string $requestedRole): string
    {
        if ($currentRole !== 'superadmin') {
            return $currentRole;
        }

        if (in_array($requestedRole, self::ALLOWED_ROLES, true)) {
            return $requestedRole;
        }

        return 'editor';
    }

    private function resolveStorageDirectory(string $role, ?Book $book): string
    {
        if (!$book) {
            return 'role-files/' . $role . '/general/' . now()->format('Y/m');
        }

        $authorName = trim((string) ($book->author?->name ?: $book->penulis_1 ?: 'unknown-author'));
        $bookCode = trim((string) ($book->nomor_naskah ?: ('book-' . $book->id)));
        $bookTitle = trim((string) ($book->judul ?: 'untitled'));

        $folder = $bookCode . '-' . $bookTitle . '-' . $authorName;

        return 'role-files/' . $role . '/books/' . Str::slug($folder);
    }

    private function resolveBookFileType(string $requestedType, string $category, string $title): ?string
    {
        $allowed = [
            'isbn_image',
            'qrcbn_image',
            'final_layout',
            'final_cover',
            'skk',
            'hki',
            'sertifikat_penulis',
            'naskah_final',
            'cover',
            'layout_pdf',
        ];

        if ($requestedType !== '' && in_array($requestedType, $allowed, true)) {
            return $requestedType;
        }

        if (in_array($category, ['skk', 'hki', 'sertifikat_penulis'], true)) {
            return $category;
        }

        $haystack = Str::lower($category . ' ' . $title);

        if (str_contains($haystack, 'qrcbn') || str_contains($haystack, 'qr c b n') || str_contains($haystack, 'qr')) {
            return 'qrcbn_image';
        }

        if (str_contains($haystack, 'isbn')) {
            return 'isbn_image';
        }

        if (str_contains($haystack, 'layout')) {
            return 'final_layout';
        }

        if (str_contains($haystack, 'cover')) {
            return 'final_cover';
        }

        return null;
    }

    private function syncBookFileFromRoleUpload(Book $book, RoleFile $roleFile, string $type, string $senderRole): void
    {
        BookFile::where('book_id', $book->id)
            ->where('type', $type)
            ->update(['is_active' => false]);

        $version = (int) (BookFile::where('book_id', $book->id)
            ->where('type', $type)
            ->max('version') ?? 0) + 1;

        BookFile::create([
            'book_id' => $book->id,
            'type' => $type,
            'original_name' => $roleFile->title,
            'note' => trim((string) ($roleFile->description ?? 'Sinkron dari Ruang File Role')),
            'sender_role' => $senderRole,
            'file_path' => $roleFile->file_path,
            'mime_type' => $roleFile->mime_type,
            'file_size' => $roleFile->file_size,
            'is_active' => true,
            'version' => $version,
        ]);
    }

    private function canAccessRoleFile(RoleFile $roleFile, string $userRole, int $userId): bool
    {
        if ($userRole === 'superadmin') {
            return true;
        }

        if ((int) $roleFile->user_id === $userId) {
            return true;
        }

        $userEmail = Str::lower((string) (auth()->user()->email ?? ''));
        $emailDomain = str_contains($userEmail, '@') ? Str::after($userEmail, '@') : '';

        $allowedEmails = (array) ($roleFile->allowed_emails ?? []);
        $allowedDomains = (array) ($roleFile->allowed_domains ?? []);
        $allowedRoles = (array) ($roleFile->allowed_roles ?? []);

        if ($userEmail !== '' && in_array($userEmail, array_map(fn($value) => Str::lower((string) $value), $allowedEmails), true)) {
            return true;
        }

        if ($emailDomain !== '' && in_array($emailDomain, array_map(fn($value) => Str::lower((string) $value), $allowedDomains), true)) {
            return true;
        }

        if (in_array($userRole, $allowedRoles, true)) {
            return true;
        }

        $scope = (string) ($roleFile->access_scope ?? 'role');

        return match ($scope) {
            'public' => true,
            'all_roles' => true,
            'role' => $roleFile->role === $userRole,
            'private' => false,
            default => $roleFile->role === $userRole,
        };
    }

    private function scopeLabel(string $scope): string
    {
        return match ($scope) {
            'private' => 'Private',
            'role' => 'Role Terkait',
            'all_roles' => 'Semua Role (Internal)',
            'public' => 'Publik (Siapa Saja)',
            default => 'Role Terkait',
        };
    }

    private function sanitizeRoles(array $roles): array
    {
        $clean = collect($roles)
            ->map(fn($role) => (string) $role)
            ->filter(fn($role) => in_array($role, self::ALLOWED_ROLES, true))
            ->unique()
            ->values()
            ->all();

        return $clean;
    }

    private function sanitizeCsvList(string $raw, bool $isEmail): array
    {
        $items = preg_split('/[\s,;\n\r]+/', trim($raw)) ?: [];

        $clean = collect($items)
            ->map(fn($item) => Str::lower(trim((string) $item)))
            ->filter()
            ->filter(function ($item) use ($isEmail) {
                if ($isEmail) {
                    return filter_var($item, FILTER_VALIDATE_EMAIL) !== false;
                }

                return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $item) === 1;
            })
            ->unique()
            ->values()
            ->all();

        return $clean;
    }

    private function logFileAccess(RoleFile $roleFile, string $action, bool $granted, string $note): void
    {
        try {
            $user = auth()->user();
            DB::table('role_file_access_logs')->insert([
                'role_file_id' => $roleFile->id,
                'user_id' => $user?->id,
                'email' => $user?->email,
                'role' => $user?->role,
                'action' => $action,
                'granted' => $granted,
                'scope' => (string) ($roleFile->access_scope ?? 'role'),
                'note' => $note,
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // no-op
        }
    }
}
