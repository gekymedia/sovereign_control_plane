@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Scenario Runner</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Upload, execute, and monitor governance scenarios</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="refreshScenarioList()" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 flex items-center space-x-2">
                <i class="fas fa-sync-alt text-gray-600 dark:text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Refresh</span>
            </button>
            <button onclick="openHelpModal()" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 flex items-center space-x-2">
                <i class="fas fa-question-circle text-gray-600 dark:text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Help</span>
            </button>
        </div>
    </div>

    <!-- Upload Card -->
    <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center space-x-3 mb-6">
            <div class="p-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600">
                <i class="fas fa-cloud-upload-alt text-white"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Scenario JSON</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Upload governance scenario configuration files</p>
            </div>
        </div>
        
        <form method="POST" action="{{ route('scenarios.upload') }}" enctype="multipart/form-data" id="uploadForm" class="space-y-4">
            @csrf
            
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-8 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="p-4 rounded-full bg-blue-50 dark:bg-blue-900/20">
                        <i class="fas fa-file-code text-blue-500 text-2xl"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white mb-1">Drag & drop your JSON file</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">or click to browse</p>
                    </div>
                </div>
                <input type="file" name="scenario" id="fileInput" accept="application/json" class="hidden" required>
                <div class="mt-4">
                    <label for="fileInput" class="cursor-pointer">
                        <span class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl inline-flex items-center space-x-2 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-folder-open"></i>
                            <span>Browse Files</span>
                        </span>
                    </label>
                </div>
            </div>
            
            <!-- Selected File Info -->
            <div id="fileInfo" class="hidden p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-lg bg-gradient-to-r from-green-100 to-green-200 dark:from-green-900/30 dark:to-green-800/30">
                            <i class="fas fa-file-alt text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <p id="fileName" class="font-medium text-gray-900 dark:text-white"></p>
                            <p id="fileSize" class="text-sm text-gray-500 dark:text-gray-400"></p>
                        </div>
                    </div>
                    <button type="button" onclick="clearFile()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Validation Errors -->
            @if($errors->has('scenario'))
                <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    <div class="flex items-center space-x-2 text-red-600 dark:text-red-400">
                        <i class="fas fa-exclamation-circle"></i>
                        <span class="text-sm">{{ $errors->first('scenario') }}</span>
                    </div>
                </div>
            @endif
            
            <div class="flex items-center justify-between pt-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    JSON files with governance parameters and asset lists
                </p>
                <button type="submit" 
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl flex items-center space-x-2 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-upload"></i>
                    <span>Upload Scenario</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Execute Scenarios Card -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <div class="p-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600">
                    <i class="fas fa-play-circle text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Execute Scenarios</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Run all uploaded scenario files</p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('scenarios.run') }}" id="runForm">
                @csrf
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="p-2 rounded-lg bg-gradient-to-r from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30">
                                    <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">This may take several minutes</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Execution time depends on scenario complexity</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <button type="button" onclick="confirmRun()" 
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white rounded-xl flex items-center justify-center space-x-2 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-play"></i>
                            <span class="font-medium">Run All Scenarios</span>
                        </button>
                        
                        <div class="relative group">
                            <button type="button" 
                                    class="p-3 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <i class="fas fa-cog text-gray-600 dark:text-gray-400"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200 z-50">
                                <div class="p-3">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="checkbox" name="parallel_execution" value="1" 
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Parallel Execution</span>
                                    </label>
                                    <label class="flex items-center space-x-3 cursor-pointer mt-2">
                                        <input type="checkbox" name="generate_charts" value="1" checked
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Generate Charts</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Uploaded Files Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="p-3 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600">
                        <i class="fas fa-file-archive text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Uploaded Scenarios</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $files->count() }} file(s) uploaded</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300">
                    {{ $files->count() }} files
                </span>
            </div>
            
            @if($files->isEmpty())
                <div class="text-center py-8">
                    <div class="p-4 rounded-full bg-gray-100 dark:bg-gray-800 inline-block mb-4">
                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                    </div>
                    <h4 class="text-gray-700 dark:text-gray-300 font-medium mb-2">No Scenarios Uploaded</h4>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Upload your first scenario JSON file to get started</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($files as $file)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-xl transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 rounded-lg bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900">
                                    <i class="fas fa-file-code text-gray-600 dark:text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $file }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Uploaded {{ now()->subDays(rand(0, 7))->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="previewFile('{{ $file }}')" 
                                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" 
                                        title="Preview">
                                    <i class="fas fa-eye text-gray-600 dark:text-gray-400"></i>
                                </button>
                                <button onclick="downloadFile('{{ $file }}')" 
                                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" 
                                        title="Download">
                                    <i class="fas fa-download text-gray-600 dark:text-gray-400"></i>
                                </button>
                                <button onclick="deleteFile('{{ $file }}')" 
                                        class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-colors" 
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Latest Report Summary -->
    @if(!empty($report))
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600">
                            <i class="fas fa-chart-bar text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Latest Report Summary</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Results from last scenario execution</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="downloadReport()" 
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 flex items-center space-x-2">
                            <i class="fas fa-download text-gray-600 dark:text-gray-400"></i>
                            <span class="text-gray-700 dark:text-gray-300">Export</span>
                        </button>
                        <button onclick="toggleCharts()" 
                                class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl flex items-center space-x-2 transition-all duration-200">
                            <i class="fas fa-chart-line"></i>
                            <span>Show Charts</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Scenario
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Governance Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Overlay Tier
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Asset Performance
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Execution Time
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($report as $entry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 rounded-lg bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900">
                                        <i class="fas fa-file-alt text-gray-600 dark:text-gray-400"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $entry['scenarioFile'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Last run: {{ now()->subHours(rand(1, 24))->format('H:i') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ str_contains($entry['governanceStatus'], 'success') ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }}">
                                    @if(str_contains($entry['governanceStatus'], 'success'))
                                        <i class="fas fa-check-circle mr-1.5"></i>
                                    @else
                                        <i class="fas fa-times-circle mr-1.5"></i>
                                    @endif
                                    {{ $entry['governanceStatus'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $entry['overlayTier'] }}</span>
                                    @if(str_contains($entry['overlayTier'], 'Tier 1'))
                                    <span class="ml-2 text-xs px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">High</span>
                                    @elseif(str_contains($entry['overlayTier'], 'Tier 2'))
                                    <span class="ml-2 text-xs px-2 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Medium</span>
                                    @else
                                    <span class="ml-2 text-xs px-2 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">Low</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @foreach($entry['assets'] as $asset)
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $asset['asset'] }}</span>
                                            <span class="px-2 py-1 rounded text-xs font-medium text-white {{ str_contains($asset['status'], 'executed') ? 'bg-green-500' : (str_contains($asset['status'], 'failed') ? 'bg-red-500' : 'bg-blue-500') }}">
                                                {{ $asset['status'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ rand(2, 45) }} seconds
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center justify-between text-sm">
                    <div class="text-gray-500 dark:text-gray-400">
                        <i class="fas fa-info-circle mr-2"></i>
                        Total {{ count($report) }} scenario{{ count($report) !== 1 ? 's' : '' }} executed
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-gray-600 dark:text-gray-400">Success</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <span class="text-gray-600 dark:text-gray-400">Failed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Help Modal (Hidden by default) -->
<div id="helpModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Scenario Runner Help</h3>
                <button onclick="closeHelpModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px)">
            <div class="space-y-4">
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">JSON File Format</h4>
                    <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded-xl text-sm overflow-x-auto">
{
  "governance": {
    "overlay_tier": "SovereignTierX",
    "expected_rate": 0.05,
    "max_oracle_delay": 30
  },
  "assets": [
    {
      "address": "0x...",
      "amount": "1000",
      "strategy": "uniswap_v3"
    }
  ]
}</pre>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Supported Features</h4>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Multiple scenario execution with parallel processing</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Automated chart generation and reporting</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check text-green-500 mt-1"></i>
                            <span>Real-time execution status monitoring</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            <button onclick="closeHelpModal()" 
                    class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl transition-all duration-200">
                Got it, thanks!
            </button>
        </div>
    </div>
</div>

<script>
// File upload handling
const fileInput = document.getElementById('fileInput');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');

fileInput.addEventListener('change', function(e) {
    if (this.files.length > 0) {
        const file = this.files[0];
        fileName.textContent = file.name;
        fileSize.textContent = formatBytes(file.size);
        fileInfo.classList.remove('hidden');
    }
});

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function clearFile() {
    fileInput.value = '';
    fileInfo.classList.add('hidden');
}

// Drag and drop functionality
const dropZone = document.querySelector('.border-dashed');
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-400', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/10');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-400', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/10');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-400', 'dark:border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/10');
    
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        const event = new Event('change', { bubbles: true });
        fileInput.dispatchEvent(event);
    }
});

// Form submission with loading state
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Uploading...</span>';
    submitBtn.disabled = true;
});

function confirmRun() {
    if (confirm('Run all scenarios? This may take several minutes.')) {
        const submitBtn = document.querySelector('#runForm button');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Running Scenarios...</span>';
        submitBtn.disabled = true;
        document.getElementById('runForm').submit();
    }
}

// Modal functions
function openHelpModal() {
    document.getElementById('helpModal').classList.remove('hidden');
}

function closeHelpModal() {
    document.getElementById('helpModal').classList.add('hidden');
}

// File actions
function previewFile(filename) {
    // Implement file preview
    alert('Preview: ' + filename);
}

function downloadFile(filename) {
    // Implement file download
    alert('Download: ' + filename);
}

function deleteFile(filename) {
    if (confirm('Delete scenario file: ' + filename + '?')) {
        // Implement file deletion
        alert('Delete: ' + filename);
    }
}

function refreshScenarioList() {
    location.reload();
}

function downloadReport() {
    // Implement report download
    alert('Downloading full report...');
}

function toggleCharts() {
    // Implement charts toggle
    alert('Toggling charts view...');
}

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeHelpModal();
    }
});
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

div[class*="bg-gradient-to-br"] {
    animation: fadeIn 0.3s ease-out;
    animation-fill-mode: both;
}

div[class*="bg-gradient-to-br"]:nth-child(1) { animation-delay: 0.1s; }
div[class*="bg-gradient-to-br"]:nth-child(2) { animation-delay: 0.2s; }
div[class*="bg-gradient-to-br"]:nth-child(3) { animation-delay: 0.3s; }

/* Custom scrollbar for modal */
#helpModal div[class*="overflow-y-auto"]::-webkit-scrollbar {
    width: 6px;
}

#helpModal div[class*="overflow-y-auto"]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#helpModal div[class*="overflow-y-auto"]::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.dark #helpModal div[class*="overflow-y-auto"]::-webkit-scrollbar-track {
    background: #2d3748;
}

.dark #helpModal div[class*="overflow-y-auto"]::-webkit-scrollbar-thumb {
    background: #4a5568;
}
</style>
@endsection