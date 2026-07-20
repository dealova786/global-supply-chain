@extends('layouts.dashboard')

@section('content')
    @php
        $positiveWords = isset($positiveWords)
            ? collect($positiveWords)
            : collect(\Illuminate\Support\Facades\DB::table('positive_words')->orderBy('word')->get());

        $negativeWords = isset($negativeWords)
            ? collect($negativeWords)
            : collect(\Illuminate\Support\Facades\DB::table('negative_words')->orderBy('word')->get());

        $totalPositive = $positiveWords->count();
        $totalNegative = $negativeWords->count();
        $totalWords = $totalPositive + $totalNegative;

        $latestPositive = \Illuminate\Support\Facades\DB::table('positive_words')
            ->latest('created_at')
            ->first();

        $latestNegative = \Illuminate\Support\Facades\DB::table('negative_words')
            ->latest('created_at')
            ->first();
    @endphp

    <div class="mb-4">
        <h1 class="page-title">Admin Sentiment Words</h1>
        <p class="page-subtitle">
            Kelola kata positif dan negatif yang digunakan dalam analisis sentimen berita pada News Intelligence.
        </p>
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
                    <i class="bi bi-chat-square-text"></i>
                </div>
                <div class="stat-label">Total Words</div>
                <div class="stat-value">{{ $totalWords }}</div>
                <div class="stat-note">Kata sentimen tersimpan</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-emoji-smile"></i>
                </div>
                <div class="stat-label">Positive Words</div>
                <div class="stat-value">{{ $totalPositive }}</div>
                <div class="stat-note">Kata bernilai positif</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-emoji-frown"></i>
                </div>
                <div class="stat-label">Negative Words</div>
                <div class="stat-value">{{ $totalNegative }}</div>
                <div class="stat-note">Kata bernilai negatif</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-activity"></i>
                </div>
                <div class="stat-label">Sentiment Method</div>
                <div class="stat-value" style="font-size: 22px;">Lexicon</div>
                <div class="stat-note">Rule-based analysis</div>
            </div>
        </div>
    </div>

    {{-- Add Word Form --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add Sentiment Word</h5>
            <p class="text-muted">
                Tambahkan kata baru ke kamus sentimen. Kata ini akan digunakan untuk menghitung sentimen berita dari GNews API.
            </p>

            <form method="POST" action="{{ route('admin.sentiment-words.store') }}">
                @csrf

                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Word</label>
                        <input type="text"
                               name="word"
                               class="form-control"
                               value="{{ old('word') }}"
                               placeholder="Contoh: growth, crisis, delay, stable"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sentiment Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">-- Pilih Type --</option>
                            <option value="positive" {{ old('type') === 'positive' ? 'selected' : '' }}>
                                Positive
                            </option>
                            <option value="negative" {{ old('type') === 'negative' ? 'selected' : '' }}>
                                Negative
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-1"></i>
                            Add
                        </button>
                    </div>
                </div>
            </form>

            <small class="text-muted d-block mt-3">
                Gunakan kata bahasa Inggris karena berita dari GNews umumnya menggunakan bahasa Inggris.
            </small>
        </div>
    </div>

    {{-- Explanation --}}
    <div class="alert alert-primary mb-4">
        <strong>Cara kerja sentiment analysis:</strong>
        sistem membaca judul dan deskripsi berita, lalu mencocokkan kata dengan daftar positive words dan negative words.
        Jika kata negatif lebih dominan, maka berita dinilai lebih berisiko terhadap rantai pasok.
    </div>

    {{-- Word Tables --}}
    <div class="row g-4">
        {{-- Positive Words --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">
                                <i class="bi bi-emoji-smile text-success me-2"></i>
                                Positive Words
                            </h5>
                            <p class="text-muted mb-0">
                                Kata yang menurunkan news risk.
                            </p>
                        </div>

                        <span class="badge bg-success">
                            {{ $totalPositive }} Words
                        </span>
                    </div>

                    @if($latestPositive)
                        <div class="alert alert-success">
                            Latest positive word:
                            <strong>{{ $latestPositive->word }}</strong>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle sentiment-table">
                            <thead>
                                <tr>
                                    <th class="col-no">No</th>
                                    <th class="col-word">Word</th>
                                    <th class="col-action text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($positiveWords as $word)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <span class="badge bg-success">
                                                {{ $word->word }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <form method="POST"
                                                      action="{{ route('admin.sentiment-words.destroy', ['type' => 'positive', 'id' => $word->id]) }}"
                                                      onsubmit="return confirm('Hapus positive word ini?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            Belum ada positive words.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        {{-- Negative Words --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">
                                <i class="bi bi-emoji-frown text-danger me-2"></i>
                                Negative Words
                            </h5>
                            <p class="text-muted mb-0">
                                Kata yang menaikkan news risk.
                            </p>
                        </div>

                        <span class="badge bg-danger">
                            {{ $totalNegative }} Words
                        </span>
                    </div>

                    @if($latestNegative)
                        <div class="alert alert-danger">
                            Latest negative word:
                            <strong>{{ $latestNegative->word }}</strong>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle sentiment-table">
                            <thead>
                                <tr>
                                    <th class="col-no">No</th>
                                    <th class="col-word">Word</th>
                                    <th class="col-action text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($negativeWords as $word)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $word->word }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <form method="POST"
                                                      action="{{ route('admin.sentiment-words.destroy', ['type' => 'negative', 'id' => $word->id]) }}"
                                                      onsubmit="return confirm('Hapus negative word ini?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            Belum ada negative words.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection