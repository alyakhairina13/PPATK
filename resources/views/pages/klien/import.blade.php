<x-layout>
    <x-slot name="title">Import Klien</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Import Data Klien</h2>
                <a href="{{ route('klien.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <span class="material-symbols-outlined text-[18px] mr-2">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Instructions -->
        <div class="px-6 py-6 bg-blue-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Petunjuk Import</h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                <li>Download template Excel di bawah ini</li>
                <li>Isi data klien sesuai dengan format yang tersedia</li>
                <li>Pastikan NIK berisi 16 digit angka dan unik (tidak ada duplikasi)</li>
                <li>Jenis Kelamin diisi dengan "Laki-laki" atau "Perempuan"</li>
                <li>Semua kolom wajib diisi, termasuk NPWP</li>
                <li>Upload file yang sudah diisi dengan format .csv, .xlsx, atau .xls</li>
            </ol>
        </div>

        <!-- Download Template -->
        <div class="px-6 py-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Download Template</h3>
            <p class="text-sm text-gray-600 mb-4">Download template CSV berisi format kolom beserta contoh pengisian untuk memudahkan import data klien</p>
            <a href="{{ route('klien.template') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <span class="material-symbols-outlined text-[18px] mr-2">download</span>
                Download Template CSV
            </a>
        </div>

        <!-- Upload Form -->
        <div class="px-6 py-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upload File Import</h3>
            
            <form method="POST" action="{{ route('klien.processImport') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih File Excel/CSV <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <span class="material-symbols-outlined mx-auto text-[48px] text-gray-400">upload_file</span>
                            <div class="flex text-sm text-gray-600">
                                <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Pilih file</span>
                                    <input 
                                        id="file" 
                                        name="file" 
                                        type="file" 
                                        class="sr-only" 
                                        accept=".xlsx,.xls,.csv"
                                        required
                                        onchange="updateFileName(this)"
                                    >
                                </label>
                                <p class="pl-1">atau drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">Excel atau CSV (Maksimal 2MB)</p>
                            <p id="fileName" class="text-sm text-gray-900 font-medium mt-2"></p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview Section (Optional) -->
                <div id="previewSection" class="hidden mb-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Preview Data</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Preview akan ditampilkan setelah file dipilih</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('klien.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="material-symbols-outlined text-[18px] inline-block align-middle mr-2">cloud_upload</span>
                        Import Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Format Information -->
        <div class="px-6 py-6 bg-gray-50 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Format Kolom Template</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kolom</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wajib</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">nama_lengkap</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Text</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ya</td>
                            <td class="px-6 py-4 text-sm text-gray-500">Nama lengkap klien</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">nik</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Number (16 digit)</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ya</td>
                            <td class="px-6 py-4 text-sm text-gray-500">NIK harus 16 digit dan unik</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">tempat_tanggal_lahir</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Text</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ya</td>
                            <td class="px-6 py-4 text-sm text-gray-500">Contoh: Jakarta, 01 Januari 1990</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">jenis_kelamin</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Text (L/P)</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ya</td>
                            <td class="px-6 py-4 text-sm text-gray-500">L untuk Laki-laki, P untuk Perempuan</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">alamat</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Text</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ya</td>
                            <td class="px-6 py-4 text-sm text-gray-500">Alamat lengkap klien</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">nomor_telepon</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Text/Number</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ya</td>
                            <td class="px-6 py-4 text-sm text-gray-500">Nomor telepon aktif</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">pekerjaan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Text</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ya</td>
                            <td class="px-6 py-4 text-sm text-gray-500">Pekerjaan klien</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">npwp</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Text</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ya</td>
                            <td class="px-6 py-4 text-sm text-gray-500">Nomor NPWP klien</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0]?.name;
            const fileNameDisplay = document.getElementById('fileName');
            
            if (fileName) {
                fileNameDisplay.textContent = 'File dipilih: ' + fileName;
                // Optionally show preview section
                // document.getElementById('previewSection').classList.remove('hidden');
            } else {
                fileNameDisplay.textContent = '';
                // document.getElementById('previewSection').classList.add('hidden');
            }
        }

        // Drag and drop functionality
        const dropZone = document.querySelector('.border-dashed');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            const fileInput = document.getElementById('file');
            fileInput.files = files;
            updateFileName(fileInput);
        }
    </script>
</x-layout>
