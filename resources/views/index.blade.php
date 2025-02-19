@extends('layouts.app')

@section('title', 'Data Izin')
@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .form-group {
        margin-bottom: 15px;
    }
    .form-label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    .table-container {
        margin-top: 20px;
        overflow-x: auto;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Back-Up Data Sicantik</h3>
        </div>
        <div class="card-body">
            <!-- Form untuk tampilkan data -->
            <form method="GET" action="{{ route('index.index')  }}">
                <!-- Pilihan Database -->
                <div class="form-group">
                    <label class="form-label">Pilih Database</label>
                    <select class="form-control w-25" name="table" id="table-select" required>
                        <option value="">-- Pilih Database --</option>
                        @foreach($database_tables as $table)
                            <option value="{{ $table }}" {{ $selected_table == $table ? 'selected' : '' }}>
                                {{ $table }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Tampilkan Data -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-table"></i> Tampilkan Data Tabel
                    </button>
                </div>
            </form>

            <!-- Form untuk backup -->
            <form method="POST" action="{{ route('index.backup') }}">
                @csrf
                <!-- Form Back-up -->
                <div class="backup-form">
                    <!-- Hidden input untuk table yang dipilih -->
                    <input type="hidden" name="table" value="{{ $selected_table }}">

                    <!-- Periode Backup -->
                    <div class="form-group">
                        <label class="form-label">Periode Back-Up</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="date" class="form-control w-25"
                                   name="start_date"
                                   value="{{ request('start_date') }}">
                            <span>s/d</span>
                            <input type="date" class="form-control w-25"
                                   name="end_date"
                                   value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success" onclick="return validateBackup()">
                        <i class="fas fa-download"></i> Back-Up Data Sicantik
                    </button>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success mt-3">
                    {!! session('success') !!}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger mt-3">
                    {!! session('error') !!}
                </div>
            @endif

            <!-- Tabel Data -->
            @if(isset($data) && count($data) > 0)
            <div class="table-container">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                            <tr>
                                @foreach($columns as $column)
                                    <td>{{ $row->$column }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".datepicker", {
            dateFormat: "d/m/Y",
            allowInput: true
        });

        // Handle klik tombol tampilkan data
        document.getElementById('showDataBtn').addEventListener('click', function() {
            const selectedTable = document.getElementById('table-select').value;
            if (selectedTable) {
                window.location.href = `{{ route('index.index') }}?table=${selectedTable}`;
            } else {
                alert('Mohon pilih database terlebih dahulu!');
            }
        });
    });

    function validateBackup() {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;

        if (!startDate || !endDate) {
            alert('Periode backup kudu diisi kabeh nek arep backup data!');
            return false;
        }
        return true;
    }
</script>
@endpush
