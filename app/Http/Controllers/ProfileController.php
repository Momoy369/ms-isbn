<?php

namespace App\Http\Controllers;

use App\Models\AuthorUpgradeRequest;
use App\Models\User;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $latestUpgradeRequest = AuthorUpgradeRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        return view('profile.edit', [
            'user' => $request->user(),
            'latestUpgradeRequest' => $latestUpgradeRequest,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, NotificationService $notifications): RedirectResponse
    {
        $data = $request->validated();

        $upgradeToAuthor = (bool) ($data['upgrade_to_author'] ?? false);
        $upgradeNote = trim((string) ($data['author_upgrade_note'] ?? ''));
        $upgradeDocument = $request->file('author_upgrade_document');
        unset($data['upgrade_to_author']);
        unset($data['author_upgrade_note']);
        unset($data['author_upgrade_document']);

        $request->user()->fill($data);

        if ($upgradeToAuthor && in_array((string) $request->user()->role, ['customer', 'reader'], true)) {
            $checklist = [
                'ktp_number' => !empty($request->user()->ktp_number),
                'ktp_name' => !empty($request->user()->ktp_name),
                'birth_date' => !empty($request->user()->birth_date),
                'phone' => !empty($request->user()->phone),
                'address' => !empty($request->user()->address),
                'bank_name' => !empty($request->user()->bank_name),
                'bank_account_number' => !empty($request->user()->bank_account_number),
                'bank_account_holder' => !empty($request->user()->bank_account_holder),
            ];

            if (in_array(false, $checklist, true)) {
                return Redirect::route('profile.edit')->with('warning', 'Lengkapi checklist data author terlebih dahulu sebelum mengajukan upgrade.');
            }

            $existingPending = AuthorUpgradeRequest::query()
                ->where('user_id', $request->user()->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first();

            $isNewSubmission = $existingPending === null;

            $documentPath = $existingPending?->supporting_document_path;

            if ($upgradeDocument) {
                if ($documentPath && Storage::disk('public')->exists($documentPath)) {
                    Storage::disk('public')->delete($documentPath);
                }

                $documentPath = $upgradeDocument->store('author-upgrade-documents', 'public');
            }

            $upgradeRequest = AuthorUpgradeRequest::query()->updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'status' => 'pending',
                ],
                [
                    'checklist' => $checklist,
                    'request_note' => $upgradeNote !== '' ? $upgradeNote : null,
                    'supporting_document_path' => $documentPath,
                    'submitted_at' => now(),
                    'reviewed_at' => null,
                    'reviewed_by_user_id' => null,
                    'review_notes' => null,
                ]
            );

            if ($isNewSubmission) {
                $adminUsers = User::query()
                    ->whereIn('role', ['admin', 'superadmin'])
                    ->get(['id']);

                foreach ($adminUsers as $adminUser) {
                    $notifications->send(
                        (int) $adminUser->id,
                        'Pengajuan Upgrade Author Baru',
                        'User ' . $request->user()->name . ' mengajukan upgrade author (request #' . $upgradeRequest->id . ').',
                        null
                    );
                }
            }
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($upgradeToAuthor && in_array((string) $request->user()->role, ['customer', 'reader'], true)) {
            return Redirect::route('profile.edit')
                ->with('status', 'profile-updated')
                ->with('success', 'Pengajuan upgrade ke author sudah dikirim dan menunggu persetujuan admin.');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
