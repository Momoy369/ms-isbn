@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h1 class="m-0">Edit User</h1>
@endsection

@section('content')
    <div class="card shadow-sm">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                            @foreach (['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'author', 'finance', 'superadmin'] as $role)
                                <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ strtoupper($role) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Status Profil Penulis</label>
                        <div class="form-control bg-light">{{ $user->is_profile_complete ? 'Lengkap' : 'Belum Lengkap' }}
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Data Identitas Penulis (Wajib jika role AUTHOR)</h5>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>No. KTP</label>
                        <input type="text" name="ktp_number"
                            class="form-control @error('ktp_number') is-invalid @enderror"
                            value="{{ old('ktp_number', $user->ktp_number) }}" placeholder="16 digit NIK">
                        @error('ktp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-8 form-group">
                        <label>Nama Sesuai KTP</label>
                        <input type="text" name="ktp_name" class="form-control @error('ktp_name') is-invalid @enderror"
                            value="{{ old('ktp_name', $user->ktp_name) }}">
                        @error('ktp_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="birth_date"
                            class="form-control @error('birth_date') is-invalid @enderror"
                            value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                        @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Alamat</label>
                        <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('users.index') }}" class="btn btn-default mr-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
