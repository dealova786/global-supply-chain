@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-subtitle">
                Perbarui data akun, role, dan password user.
            </p>
        </div>

        <a href="{{ url('/admin/users') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Edit User Form</h5>

                    <form method="POST" action="{{ url('/admin/users/' . $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nama User</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>

                            @if($user->id === auth()->id())
                                <select name="role" class="form-select" disabled>
                                    <option value="admin" selected>Admin</option>
                                </select>

                                <input type="hidden" name="role" value="admin">

                                <small class="text-muted">
                                    Role akun yang sedang login dikunci agar admin tidak kehilangan akses.
                                </small>
                            @else
                                <select name="role" class="form-select" required>
                                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>
                                        User
                                    </option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                                        Admin
                                    </option>
                                </select>
                            @endif
                        </div>

                        <div class="alert alert-secondary">
                            Kosongkan password jika tidak ingin mengubah password user.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Opsional">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       placeholder="Opsional">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ url('/admin/users') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                         style="width: 72px; height: 72px; background: #e0edff; color: #2563eb; font-size: 28px; font-weight: 800;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    <div class="mb-3">
                        @if($user->role === 'admin')
                            <span class="badge bg-danger">Admin</span>
                        @else
                            <span class="badge bg-success">User</span>
                        @endif

                        @if($user->id === auth()->id())
                            <span class="badge bg-primary">Current User</span>
                        @endif
                    </div>

                    <div class="small text-muted">
                        <div class="mb-2">
                            <strong>Created:</strong><br>
                            {{ $user->created_at ? $user->created_at->format('d M Y H:i') : '-' }}
                        </div>

                        <div>
                            <strong>Last Updated:</strong><br>
                            {{ $user->updated_at ? $user->updated_at->format('d M Y H:i') : '-' }}
                        </div>
                    </div>

                    @if($user->id === auth()->id())
                        <div class="alert alert-warning mt-4 mb-0">
                            Anda sedang mengedit akun yang sedang digunakan. Role admin dikunci agar akses tidak hilang.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection