@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    
    <!-- Tombol Kembali (Diposisikan paling atas dan aman dari tumpukan elemen) -->
    <div>
        <button onclick="history.back()" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-sm transition-all cursor-pointer relative z-20">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </button>
    </div>

    <!-- Header Navigation -->
    <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="text-2xl">
                @if(in_array($extension, ['doc', 'docx']))
                    <i class="fa-solid fa-file-word text-blue-500"></i>
                @elseif(in_array($extension, ['xls', 'xlsx']))
                    <i class="fa-solid fa-file-excel text-emerald-500"></i>
                @elseif($extension === 'pdf')
                    <i class="fa-solid fa-file-pdf text-rose-500"></i>
                @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']))
                    <i class="fa-solid fa-file-image text-purple-500"></i>
                @else
                    <i class="fa-solid fa-file text-gray-400"></i>
                @endif
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ $file->file_name }}</h2>
                <p class="text-xs text-gray-400">Pratinjau Berkas Client-Side</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('files.download', $file->id) }}" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-download"></i> Unduh File
            </a>
        </div>
    </div>

    <!-- Container Utama Penampil File -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 min-h-[600px] overflow-auto relative">
        
        <!-- Loading State (Diberi z-index 10 agar tidak menembus keluar container utama) -->
        <div id="loading" class="absolute inset-0 bg-white/90 flex flex-col items-center justify-center gap-2 z-10 transition-opacity duration-300">
            <i class="fa-solid fa-circle-notch fa-spin text-3xl text-indigo-600"></i>
            <span class="text-xs font-medium text-gray-500">Memuat pratinjau dokumen...</span>
        </div>

        <!-- Render Target untuk DOCX -->
        <div id="docx-container" class="hidden flex justify-center"></div>

        <!-- Render Target untuk XLSX -->
        <div id="xlsx-container" class="hidden overflow-x-auto"></div>

        <!-- Render Target untuk PDF -->
        <div id="pdf-container" class="hidden w-full h-[750px]">
            <iframe id="pdf-frame" class="w-full h-full rounded-xl border-0"></iframe>
        </div>

        <!-- Render Target untuk Gambar -->
        <div id="image-container" class="hidden flex justify-center items-center py-4">
            <img id="image-preview" src="" alt="Pratinjau Gambar" class="max-w-full max-h-[700px] rounded-xl shadow-md object-contain">
        </div>

        <!-- Fallback Unsupported File -->
        <div id="unsupported-container" class="hidden text-center py-20">
            <i class="fa-solid fa-file-circle-exclamation text-4xl text-amber-500 mb-3"></i>
            <p class="text-sm font-semibold text-gray-700">Format file ini tidak mendukung preview langsung.</p>
            <p class="text-xs text-gray-400 mt-1">Silakan unduh file untuk melihat isinya.</p>
        </div>
    </div>
</div>

<!-- Library JavaScript Client-Side Viewer -->
<script src="https://unpkg.com/jszip/dist/jszip.min.js"></script>
<script src="https://unpkg.com/docx-preview/dist/docx-preview.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<style>
    /* Styling Tabel Excel */
    #xlsx-container table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    #xlsx-container th, #xlsx-container td {
        border: 1px solid #e5e7eb;
        padding: 8px 12px;
        text-align: left;
    }
    #xlsx-container th {
        background-color: #f9fafb;
        font-weight: 600;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileUrl = "{{ route('files.raw', $file->id) }}";
        const extension = "{{ $extension }}";

        const loading = document.getElementById('loading');
        const docxContainer = document.getElementById('docx-container');
        const xlsxContainer = document.getElementById('xlsx-container');
        const pdfContainer = document.getElementById('pdf-container');
        const pdfFrame = document.getElementById('pdf-frame');
        const imageContainer = document.getElementById('image-container');
        const imagePreview = document.getElementById('image-preview');
        const unsupportedContainer = document.getElementById('unsupported-container');

        // Fungsi aman untuk menghilangkan indikator loading
        function hideLoading() {
            if (loading) {
                loading.style.opacity = '0';
                setTimeout(() => loading.classList.add('hidden'), 300);
            }
        }

        if (['docx'].includes(extension)) {
            fetch(fileUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil file.');
                    return response.blob();
                })
                .then(blob => {
                    docxContainer.classList.remove('hidden');
                    docx.renderAsync(blob, docxContainer)
                        .then(() => hideLoading())
                        .catch(err => handleRenderError(err));
                })
                .catch(err => handleRenderError(err));

        } else if (['xlsx', 'xls'].includes(extension)) {
            fetch(fileUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil file.');
                    return response.arrayBuffer();
                })
                .then(buffer => {
                    xlsxContainer.classList.remove('hidden');
                    const workbook = XLSX.read(buffer, { type: 'array' });
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];

                    const htmlTable = XLSX.utils.sheet_to_html(worksheet);
                    xlsxContainer.innerHTML = htmlTable;
                    
                    hideLoading();
                })
                .catch(err => handleRenderError(err));

        } else if (extension === 'pdf') {
            pdfContainer.classList.remove('hidden');
            pdfFrame.src = fileUrl;
            pdfFrame.onload = function () {
                hideLoading();
            };

        } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(extension)) {
            imageContainer.classList.remove('hidden');
            imagePreview.src = fileUrl;
            imagePreview.onload = function () {
                hideLoading();
            };
            imagePreview.onerror = function (err) {
                handleRenderError(err);
            };

        } else {
            hideLoading();
            unsupportedContainer.classList.remove('hidden');
        }

        function handleRenderError(err) {
            console.error(err);
            hideLoading();
            unsupportedContainer.classList.remove('hidden');
        }
    });
</script>
@endsection