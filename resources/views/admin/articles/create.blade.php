@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Add New Article</h1>
            <p class="page-subtitle">
                Tambahkan artikel edukasi baru untuk mendukung pemahaman user terhadap risiko rantai pasok.
            </p>
        </div>

        <a href="{{ url('/admin/articles') }}" class="btn btn-secondary">
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
                    <h5 class="mb-3">Article Form</h5>

                    <form method="POST" action="{{ url('/admin/articles') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Judul Artikel</label>
                            <input type="text"
                                   name="title"
                                   class="form-control"
                                   value="{{ old('title') }}"
                                   placeholder="Contoh: Risiko Cuaca terhadap Rantai Pasok Global"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text"
                                   name="category"
                                   class="form-control"
                                   value="{{ old('category') }}"
                                   placeholder="Contoh: Risiko Cuaca, Risiko Kurs, Risk Scoring">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Publikasi</label>
                            <input type="datetime-local"
                                   name="published_at"
                                   class="form-control"
                                   value="{{ old('published_at') }}">

                            <small class="text-muted">
                                Kosongkan jika artikel masih ingin disimpan sebagai draft.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Isi Artikel</label>
                            <textarea name="content"
                                      class="form-control"
                                      rows="12"
                                      placeholder="Tulis isi artikel di sini..."
                                      required>{{ old('content') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ url('/admin/articles') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Save Article
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
                        <i class="bi bi-newspaper"></i>
                    </div>

                    <h5>Article Information</h5>

                    <div class="alert alert-primary mt-3">
                        <strong>Fungsi Artikel</strong><br>
                        Artikel digunakan sebagai konten edukasi untuk menjelaskan konsep risiko rantai pasok,
                        API monitoring, dan pengambilan keputusan berbasis data.
                    </div>

                    <div class="alert alert-success mb-0">
                        <strong>Tips Konten</strong><br>
                        Gunakan bahasa yang jelas, singkat, dan relevan dengan fitur sistem seperti weather risk,
                        currency risk, port tracking, news intelligence, dan risk scoring.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection