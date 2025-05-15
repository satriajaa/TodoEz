@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Daftar Task</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                Buat Task Baru
            </button>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('tasks.index') }}" method="GET">
                    <!-- Baris 1: Search + Category -->
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Cari task..." value="{{ request('search') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">Semua Prioritas</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium
                                </option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                    </div>

                    <!-- Baris 2: Status + Tombol -->
                    <div class="row g-2 align-items-center">
                        <div class="col-6 col-md-3">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                            </select>
                        </div>
                        <div class="col-6 col-md-9 text-md-end">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- -->
        {{-- <div class="row mb-3">
            <div class="col-md-2">
                <form method="GET" action="{{ route('tasks.index') }}">
                    <select name="perPage" class="form-select" onchange="this.form.submit()">
                        @foreach ([10, 25, 50, 100] as $perPage)
                            <option value="{{ $perPage }}"
                                {{ request('perPage', 10) == $perPage ? 'selected' : '' }}>
                                Tampilkan {{ $perPage }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Simpan semua parameter filter -->
                    @foreach (request()->except('perPage', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>
            </div>
        </div> --}}

        <!-- Tabel Task -->
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Kategori</th>
                                <th>Pioritas</th>
                                <th>Pengulangan</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tasks as $task)
                                <tr class="{{ $task->status ? 'table-success' : '' }}">
                                    <td>{{ $task->title }}</td>
                                    <td>{{ Str::limit($task->description, 50) }}</td>
                                    <td>
                                        @if ($task->category)
                                            <span class="badge" style="background:{{ $task->category->color }}">
                                                {{ $task->category->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Tanpa Kategori</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($task->priority == 'high')
                                            <span class="badge bg-danger">High</span>
                                        @elseif($task->priority == 'medium')
                                            <span class="badge bg-warning">Medium</span>
                                        @else
                                            <span class="badge bg-success">Low</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($task->recurring_pattern)
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-info mb-1">
                                                    {{ ucfirst($task->recurring_pattern) }}
                                                </span>
                                                @if ($task->recurring_until)
                                                    <span class="text-muted small">
                                                        Sampai: {{ $task->recurring_until->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($task->deadline)
                                            {{ $task->deadline->format('d/m/Y') }}
                                            @if ($task->deadline->isPast() && !$task->status)
                                                <span class="badge bg-danger">Terlambat</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('tasks.toggle-status', $task) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-sm {{ $task->status ? 'btn-success' : 'btn-warning' }}">
                                                {{ $task->status ? 'Selesai' : 'Pending' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#editTaskModal{{ $task->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit untuk Setiap Task -->
                                <div class="modal fade" id="editTaskModal{{ $task->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('tasks.update', $task) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Task</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Judul Task</label>
                                                        <input type="text" class="form-control" name="title"
                                                            value="{{ $task->title }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Deskripsi</label>
                                                        <textarea class="form-control" name="description" rows="3">{{ $task->description }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Deadline</label>
                                                        <input type="date" class="form-control" name="deadline"
                                                            value="{{ $task->deadline ? $task->deadline->format('Y-m-d') : '' }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Kategori</label>
                                                        <select class="form-select" name="category_id">
                                                            <option value="">Pilih Kategori</option>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{ $task->category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    {{-- livewire edit --}}
                                                    @livewire('recurring-task-form', [
                                                        'recurringPattern' => $task->recurring_pattern,
                                                        'recurringUntil' => optional($task->recurring_until)->format('Y-m-d'),
                                                    ])

                                                    <div class="mb-3">
                                                        <label class="form-label">Prioritas</label>
                                                        <select class="form-select" name="priority" required>
                                                            <option value="low"
                                                                {{ $task->priority == 'low' ? 'selected' : '' }}>Low
                                                            </option>
                                                            <option value="medium"
                                                                {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium
                                                            </option>
                                                            <option value="high"
                                                                {{ $task->priority == 'high' ? 'selected' : '' }}>High
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3 form-check">
                                                        <input type="checkbox" class="form-check-input" name="status"
                                                            id="status{{ $task->id }}"
                                                            {{ $task->status ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="status{{ $task->id }}">Status
                                                            Selesai</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-primary">Simpan
                                                        Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada task ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $tasks->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create -->
        <div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Buat Task Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Judul Task</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deadline</label>
                                <input type="date" class="form-control @error('deadline') is-invalid @enderror"
                                    name="deadline" value="{{ old('deadline') }}">
                                @error('deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" name="category_id">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $task->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Pengulangan</label>
                                <select class="form-select" name="recurring_pattern" id="recurringPatternSelect">
                                    <option value="">Tidak Diulang</option>
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                    <option value="yearly">Tahunan</option>
                                </select>
                            </div>
                            <div class="mb-3" id="recurringUntilContainer" style="display:none;">
                                <label class="form-label">Tanggal terakhir</label>
                                <input type="date" class="form-control" name="recurring_until"
                                    min="{{ now()->format('Y-m-d') }}">
                                <small class="text-muted">Wajib diisi untuk task berulang</small>
                                @if ($errors->has('recurring_until'))
                                    <div class="alert alert-danger mt-2">
                                        {{ $errors->first('recurring_until') }}
                                    </div>
                                @endif
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Prioritas</label>
                                <select class="form-select" name="priority" required>
                                    <option value="low"
                                        {{ old('priority', $task->priority ?? 'medium') == 'low' ? 'selected' : '' }}>Low
                                    </option>
                                    <option value="medium"
                                        {{ old('priority', $task->priority ?? 'medium') == 'medium' ? 'selected' : '' }}>
                                        Medium</option>
                                    <option value="high"
                                        {{ old('priority', $task->priority ?? 'medium') == 'high' ? 'selected' : '' }}>High
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const patternSelect = document.getElementById('recurringPatternSelect');
            const untilContainer = document.getElementById('recurringUntilContainer');

            if (patternSelect && untilContainer) {
                patternSelect.addEventListener('change', function() {
                    untilContainer.style.display = this.value ? 'block' : 'none';
                });

                // Inisialisasi saat modal dibuka
                const createModal = document.getElementById('createTaskModal');
                if (createModal) {
                    createModal.addEventListener('shown.bs.modal', function() {
                        untilContainer.style.display = patternSelect.value ? 'block' : 'none';
                    });
                }
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle recurring until field
            const patternSelect = document.querySelector('[name="recurring_pattern"]');
            const untilContainer = document.getElementById('recurringUntilContainer');

            if (patternSelect && untilContainer) {
                patternSelect.addEventListener('change', function() {
                    untilContainer.style.display = this.value ? 'block' : 'none';
                });
            }

            // Client-side validation
            const form = document.querySelector('#createTaskModal form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const pattern = form.querySelector('[name="recurring_pattern"]').value;
                    const deadline = form.querySelector('[name="deadline"]').value;
                    const until = form.querySelector('[name="recurring_until"]').value;

                    if (pattern) {
                        if (!deadline) {
                            alert('Deadline wajib diisi untuk task berulang');
                            e.preventDefault();
                            return;
                        }
                        if (!until) {
                            alert('Tanggal terakhir wajib diisi untuk task berulang');
                            e.preventDefault();
                            return;
                        }
                        if (new Date(until) <= new Date(deadline)) {
                            alert('Tanggal terakhir harus setelah deadline');
                            e.preventDefault();
                            return;
                        }
                    }
                });
            }
        });
        document.addEventListener('livewire:init', function() {
            // Handle modal show event untuk Livewire
            $('.modal').on('shown.bs.modal', function() {
                Livewire.rescan();
            });
        });
    </script>

@endsection
