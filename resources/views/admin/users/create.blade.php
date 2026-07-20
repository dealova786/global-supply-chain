@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Add New User</h1>
            <p class="page-subtitle">
                Tambahkan akun baru ke dalam sistem dan tentukan role aksesnya.
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
                    <h5 class="mb-3">User Form</h5>

                    <form method="POST" action="{{ url('/admin/users') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nama User</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama user"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email') }}"
                                   placeholder="contoh@email.com"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>
                                    User
                                </option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                    Admin
                                </option>
                            </select>

                            <small class="text-muted">
                                Admin memiliki akses ke pengelolaan sistem, sedangkan user hanya untuk monitoring.
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Minimal 8 karakter"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       placeholder="Ulangi password"
                                       required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ url('/admin/users') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Save User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="stat-icon primary mb-3">
                        <i class="bi bi-person-plus"></i>
                    </div>

                    <h5>Role Information</h5>

                    <div class="alert alert-primary mt-3">
                        <strong>Admin</strong><br>
                        Dapat mengelola user, artikel, port, sentiment words, serta akses dashboard admin.
                    </div>

                    <div class="alert alert-success mb-0">
                        <strong>User</strong><br>
                        Dapat mengakses dashboard monitoring, country dashboard, risk score, map, news, currency, compare, dan watchlist.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection