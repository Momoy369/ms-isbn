<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users =
            User::latest()
                ->get();

        return view(
            'users.index',
            compact(
                'users'
            )
        );
    }

    public function create()
    {
        return view(
            'users.create'
        );
    }

    public function store(
        Request $request
    ) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'author'])],
            'ktp_number' => ['nullable', 'string', 'max:32', 'unique:users,ktp_number'],
            'ktp_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
        ]);

        if ($data['role'] === 'author') {
            $request->validate([
                'ktp_number' => ['required', 'string', 'max:32', 'unique:users,ktp_number'],
                'ktp_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:32'],
                'address' => ['required', 'string'],
                'birth_date' => ['required', 'date'],
            ]);
        }

        $isProfileComplete = ($data['role'] !== 'author')
            || (
                !empty($data['ktp_number'])
                && !empty($data['ktp_name'])
                && !empty($data['phone'])
                && !empty($data['address'])
                && !empty($data['birth_date'])
            );

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
            'ktp_number' => $data['ktp_number'] ?? null,
            'ktp_name' => $data['ktp_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'is_profile_complete' => $isProfileComplete,
        ]);

        return redirect()
            ->route(
                'users.index'
            );
    }

    public function edit(
        User $user
    ) {
        return view(
            'users.edit',
            compact(
                'user'
            )
        );
    }

    public function update(
        Request $request,
        User $user
    ) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'author'])],
            'ktp_number' => ['nullable', 'string', 'max:32', Rule::unique('users', 'ktp_number')->ignore($user->id)],
            'ktp_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
        ]);

        if ($data['role'] === 'author') {
            $request->validate([
                'ktp_number' => ['required', 'string', 'max:32', Rule::unique('users', 'ktp_number')->ignore($user->id)],
                'ktp_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:32'],
                'address' => ['required', 'string'],
                'birth_date' => ['required', 'date'],
            ]);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'ktp_number' => $data['ktp_number'] ?? null,
            'ktp_name' => $data['ktp_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'is_profile_complete' => ($data['role'] !== 'author')
                || (
                    !empty($data['ktp_number'])
                    && !empty($data['ktp_name'])
                    && !empty($data['phone'])
                    && !empty($data['address'])
                    && !empty($data['birth_date'])
                ),
        ]);

        return redirect()
            ->route(
                'users.index'
            );
    }

    public function destroy(
        User $user
    ) {

        $user->delete();

        return back();
    }
}