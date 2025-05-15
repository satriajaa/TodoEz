@extends('layouts.app')

@section('title', 'Detail Task')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Detail Task</h1>
            <a href="{{ route('tasks.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Header dengan tombol aksi -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h3 class="mb-1">{{ $task->title }}</h3>
                        @if ($task->category)
                            <span class="badge" style="background:{{ $task->category->color }}">
                                {{ $task->category->name }}
                            </span>
                        @endif
                    </div>
                    <div class="btn-group gap-2">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#editTaskModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Informasi Utama -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="text-muted">Deskripsi</h5>
                            <p>{{ $task->description ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="text-muted">Prioritas</h5>
                            @if ($task->priority == 'high')
                                <span class="badge bg-danger">High</span>
                            @elseif($task->priority == 'medium')
                                <span class="badge bg-warning">Medium</span>
                            @else
                                <span class="badge bg-success">Low</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <h5 class="text-muted">Status</h5>
                            <form action="{{ route('tasks.toggle-status', $task) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="btn btn-sm {{ $task->status ? 'btn-success' : 'btn-warning' }}">
                                    {{ $task->status ? 'Selesai' : 'Pending' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Informasi Tambahan -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Deadline</h6>
                            </div>
                            <div class="card-body">
                                @if ($task->deadline)
                                    <p class="mb-1">{{ $task->deadline->format('d M Y') }}</p>
                                    @if ($task->deadline->isPast() && !$task->status)
                                        <span class="badge bg-danger">Terlambat</span>
                                    @endif
                                @else
                                    <p>-</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Pengulangan</h6>
                            </div>
                            <div class="card-body">
                                @if ($task->recurring_pattern)
                                    <p class="mb-1">
                                        <span class="badge bg-info">
                                            {{ ucfirst($task->recurring_pattern) }}
                                        </span>
                                    </p>
                                    @if ($task->recurring_until)
                                        <small class="text-muted">
                                            Sampai: {{ $task->recurring_until->format('d M Y') }}
                                        </small>
                                    @endif
                                @else
                                    <p>-</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Timestamps</h6>
                            </div>
                            <div class="card-body">
                                <small class="text-muted">
                                    Dibuat: {{ $task->created_at->format('d M Y H:i') }}<br>
                                    Diupdate: {{ $task->updated_at->format('d M Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Edit -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tasks.update', $task) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Task</label>
                            <input type="text" class="form-control" name="title" value="{{ $task->title }}" required>
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

                        @livewire('recurring-task-form', [
                            'recurringPattern' => $task->recurring_pattern,
                            'recurringUntil' => optional($task->recurring_until)->format('Y-m-d'),
                        ])

                        <div class="mb-3">
                            <label class="form-label">Prioritas</label>
                            <select class="form-select" name="priority" required>
                                <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="status" id="status"
                                {{ $task->status ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Status Selesai</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi modal edit
            const editModal = document.getElementById('editTaskModal');
            if (editModal) {
                editModal.addEventListener('shown.bs.modal', function() {
                    // Inisialisasi komponen Livewire jika diperlukan
                    if (typeof Livewire !== 'undefined') {
                        Livewire.rescan();
                    }
                });
            }

            // Handle recurring pattern visibility
            const patternSelect = document.querySelector('#editTaskModal [name="recurring_pattern"]');
            const untilContainer = document.querySelector('#editTaskModal #recurringUntilContainer');

            if (patternSelect && untilContainer) {
                patternSelect.addEventListener('change', function() {
                    untilContainer.style.display = this.value ? 'block' : 'none';
                });

                // Set initial state
                untilContainer.style.display = patternSelect.value ? 'block' : 'none';
            }
        });
    </script>
@endsection
