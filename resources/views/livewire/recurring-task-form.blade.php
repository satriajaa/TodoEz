<div>
    <!-- Input Pola Pengulangan -->
    <div class="mb-3">
        <label class="form-label">Pengulangan</label>
        <select class="form-select" wire:model="recurringPattern" name="recurring_pattern" required>
            <option value="">Tidak Diulang</option>
            <option value="daily">Harian</option>
            <option value="weekly">Mingguan</option>
            <option value="monthly">Bulanan</option>
            <option value="yearly">Tahunan</option>
        </select>
    </div>

    @if ($recurringPattern)
        <div class="mb-3">
            <label class="form-label">Tanggal terakhir</label>
            <input type="date" class="form-control @error('recurring_until') is-invalid @enderror"
                wire:model="recurringUntil" name="recurring_until" min="{{ now()->format('Y-m-d') }}">
            <small class="text-muted">Wajib diisi untuk task berulang</small>
            @error('recurring_until')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif
</div>

{{-- Because she competes with no one, no one can compete with her. --}}
