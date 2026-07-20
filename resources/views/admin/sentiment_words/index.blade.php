@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Sentiment Words</h3>
        <p class="text-muted">
            Halaman ini digunakan admin untuk mengelola kata positif dan negatif yang digunakan dalam sentiment analysis berita.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <style>
        .sentiment-table {
            width: auto;
            min-width: 420px;
        }

        .sentiment-table .col-no {
            width: 50px;
        }

        .sentiment-table .col-word {
            width: 180px;
        }

        .sentiment-table .col-action {
            width: 120px;
        }
    </style>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5>Add Sentiment Word</h5>
            <p class="text-muted">
            </p>

            <form method="POST" action="{{ route('admin.sentiment-words.store') }}">
                @csrf

                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Word</label>
                        <input type="text"
                               name="word"
                               class="form-control @error('word') is-invalid @enderror"
                               value="{{ old('word') }}"
                               placeholder="Contoh: crisis, growth, delay"
                               required>

                        @error('word')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type"
                                class="form-select @error('type') is-invalid @enderror"
                                required>
                            <option value="">-- Pilih Type --</option>
                            <option value="positive" {{ old('type') === 'positive' ? 'selected' : '' }}>
                                Positive
                            </option>
                            <option value="negative" {{ old('type') === 'negative' ? 'selected' : '' }}>
                                Negative
                            </option>
                        </select>

                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Word
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">
                        Positive Words
                        <span class="badge bg-success">{{ $positiveWords->count() }}</span>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="col-no">No</th>
                                    <th class="col-word">Word</th>
                                    <th class="col-action">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($positiveWords as $word)
                                    <tr>
                                        <td class="col-no">{{ $loop->iteration }}</td>
                                        <td class="col-word">
                                            <span class="badge bg-success">
                                                {{ $word->word }}
                                            </span>
                                        </td>
                                        <td class="col-action">
                                            <form method="POST"
                                                  action="{{ route('admin.sentiment-words.destroy', ['type' => 'positive', 'id' => $word->id]) }}"
                                                  onsubmit="return confirm('Hapus kata ini?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
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

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">
                        Negative Words
                        <span class="badge bg-danger">{{ $negativeWords->count() }}</span>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Word</th>
                                    <th>Action</th>
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
                                            <form method="POST"
                                                  action="{{ route('admin.sentiment-words.destroy', ['type' => 'negative', 'id' => $word->id]) }}"
                                                  onsubmit="return confirm('Hapus kata ini?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
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