@extends('layouts.dashboard')

@section('content')
    @php
        $users = isset($users)
            ? $users
            : \App\Models\User::latest()->get();

        $userCollection = collect($users instanceof \Illuminate\Pagination\AbstractPaginator ? $users->items() : $users);

        $totalUsers = \App\Models\User::count();
        $totalAdmins = \App\Models\User::where('role', 'admin')->count();
        $totalRegularUsers = \App\Models\User::where('role', 'user')->count();
        $latestUser = \App\Models\User::latest()->first();
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Admin Users</h1>
            <p class="page-subtitle">
                Kelola akun pengguna, role admin, dan akses user dalam sistem Supply Chain Risk.
            </p>
        </div>

        <a href="{{ url('/admin/users/create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add User
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

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

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ $totalUsers }}</div>
                <div class="stat-note">Semua akun sistem</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div class="stat-label">Admin</div>
                <div class="stat-value">{{ $totalAdmins }}</div>
                <div class="stat-note">Akses pengelola sistem</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-person"></i>
                </div>
                <div class="stat-label">Regular User</div>
                <div class="stat-value">{{ $totalRegularUsers }}</div>
                <div class="stat-note">Akses monitoring user</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-label">Latest User</div>
                <div class="stat-value" style="font-size: 22px;">
                    {{ $latestUser?->name ?? '-' }}
                </div>
                <div class="stat-note">
                    {{ $latestUser ? $latestUser->created_at->diffForHumans() : 'No data' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">Users Dataset</h5>
                    <p class="text-muted mb-0">
                        Daftar akun yang terdaftar pada sistem.
                    </p>
                </div>

                <span class="badge bg-primary">
                    {{ $totalUsers }} Accounts
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Email Verified</th>
                            <th>Created</th>
                            <th>Last Updated</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 38px; height: 38px; background: #e0edff; color: #2563eb; font-weight: 800;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <strong>{{ $user->name }}</strong>

                                            @if($user->id === auth()->id())
                                                <span class="badge bg-primary ms-1">You</span>
                                            @endif

                                            <br>
                                            <small class="text-muted">ID: {{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $user->email }}</td>

                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-lock me-1"></i>
                                            Admin
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-person me-1"></i>
                                            User
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">Verified</span>
                                    @else
                                        <span class="badge bg-secondary">Not Verified</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $user->created_at ? $user->created_at->diffForHumans() : '-' }}
                                </td>

                                <td>
                                    {{ $user->updated_at ? $user->updated_at->diffForHumans() : '-' }}
                                </td>

                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('/admin/users/' . $user->id . '/edit') }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        @if($user->id !== auth()->id())
                                            <form method="POST"
                                                  action="{{ url('/admin/users/' . $user->id) }}"
                                                  onsubmit="return confirm('Hapus user ini dari sistem?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                Current User
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Belum ada data user.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($users, 'links'))
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection