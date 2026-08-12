@extends('layouts.app')

@section('title', 'Upload CSV')

@section('content')
    <h1 class="page-title">Import products from a CSV</h1>
    <p class="page-subtitle">
        <a href="{{ route('sample.csv.download') }}">
    Download Sample CSV
</a></p>

    <div class="card">
        <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data" id="upload-form" novalidate>
            @csrf

            <div class="field">
                <label for="csv_file">CSV file</label>

                <label class="dropzone" id="dropzone" for="csv_file">
                    <div class="dropzone__icon">📄</div>
                    <div>Drag and drop your CSV here, or click to browse</div>
                    <div class="hint">.csv files only, up to 5MB</div>
                    <div class="dropzone__filename" id="dropzone-filename"></div>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv">
                </label>

                <div class="error" id="client-error" style="display:none;"></div>

                @error('csv_file')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn" id="submit-btn">Upload &amp; start import</button>
        </form>
    </div>

    <div class="card">
        <strong>Columns Must be in CSV</strong>
        <p class="text-muted small" style="margin-top:6px;">
            Handle, Title, Body HTML, Vendor, Product Type, Tags, Published, Variant SKU, Variant Price,
            Variant Compare At Price, Variant Inventory Qty, Variant Weight, Variant Weight Unit, Image Src, Image Alt Text.
            Only
        </p>
        <p class="text-muted small" style="margin-top:6px;">
             <strong>Title</strong> is required
        </p>
    </div>

    <script>
        (function () {
            const form = document.getElementById('upload-form');
            const input = document.getElementById('csv_file');
            const dropzone = document.getElementById('dropzone');
            const filenameEl = document.getElementById('dropzone-filename');
            const errorEl = document.getElementById('client-error');
            const submitBtn = document.getElementById('submit-btn');

            const MAX_SIZE = 5 * 1024 * 1024; // 5MB

            function showError(message) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            }

            function clearError() {
                errorEl.textContent = '';
                errorEl.style.display = 'none';
            }

            function validateFile(file) {
                if (!file) return false;

                const isCsv = file.type === 'text/csv'
                    || file.type === 'application/vnd.ms-excel'
                    || file.name.toLowerCase().endsWith('.csv');

                if (!isCsv) {
                    showError('Please select a .csv file.');
                    return false;
                }

                if (file.size > MAX_SIZE) {
                    showError('That file is larger than 5MB.');
                    return false;
                }

                clearError();
                return true;
            }

            input.addEventListener('change', function () {
                const file = input.files[0];

                if (validateFile(file)) {
                    filenameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                } else {
                    filenameEl.textContent = '';
                    input.value = '';
                }
            });

            ['dragover', 'dragenter'].forEach(evt => {
                dropzone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    dropzone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'dragend', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, function (e) {
                    dropzone.classList.remove('is-dragover');
                });
            });

            dropzone.addEventListener('drop', function (e) {
                e.preventDefault();
                const file = e.dataTransfer.files[0];

                if (validateFile(file)) {
                    input.files = e.dataTransfer.files;
                    filenameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                }
            });

            form.addEventListener('submit', function (e) {
                const file = input.files[0];

                if (!validateFile(file)) {
                    e.preventDefault();
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Uploading…';
            });
        })();
    </script>
@endsection
