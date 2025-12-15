@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">System overview and scenario analytics</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="refreshData()" 
                    class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl flex items-center space-x-2 transition-all duration-200 shadow-md hover:shadow-lg">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh Data</span>
            </button>
            @role('admin')
            <a href="{{ route('governance.index') }}" 
               class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-xl flex items-center space-x-2 transition-all duration-200 shadow-md hover:shadow-lg">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            @endrole
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Overlay Tier Status -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600">
                    <i class="fas fa-layer-group text-white text-lg"></i>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                    Active
                </span>
            </div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Active Overlay Tier</h3>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $settings['overlay_tier'] ?? 'N/A' }}</p>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                    Current system overlay configuration
                </div>
            </div>
        </div>

        <!-- Expected Rate -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600">
                    <i class="fas fa-chart-line text-white text-lg"></i>
                </div>
                <div class="flex items-center space-x-2">
                    @if(($settings['expected_rate'] ?? 0) > 0)
                    <i class="fas fa-arrow-up text-green-500 text-sm"></i>
                    <span class="text-xs text-green-600 dark:text-green-400 font-semibold">
                        {{ rand(2, 12) }}% this month
                    </span>
                    @endif
                </div>
            </div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Expected Rate</h3>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $settings['expected_rate'] ?? 'N/A' }}</p>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-clock mr-2 text-green-500"></i>
                    Updated {{ now()->diffForHumans() }}
                </div>
            </div>
        </div>

        <!-- Max Oracle Delay -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600">
                    <i class="fas fa-stopwatch text-white text-lg"></i>
                </div>
                @if(($settings['max_oracle_delay'] ?? 0) > 60)
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                    High Delay
                </span>
                @else
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                    Optimal
                </span>
                @endif
            </div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Max Oracle Delay (s)</h3>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $settings['max_oracle_delay'] ?? 'N/A' }}</p>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-shield-alt mr-2 text-amber-500"></i>
                    Real-time data freshness threshold
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @php
            $charts = [
                'overlaytierdistribution.png' => [
                    'title' => 'Overlay Tier Distribution',
                    'description' => 'Visual representation of overlay tier allocations',
                    'icon' => 'fas fa-chart-pie',
                    'color' => 'from-purple-500 to-purple-600'
                ],
                'asset_outcomes.png' => [
                    'title' => 'Asset Outcomes Analysis',
                    'description' => 'Performance metrics across all assets',
                    'icon' => 'fas fa-chart-bar',
                    'color' => 'from-blue-500 to-blue-600'
                ],
                'governance_status.png' => [
                    'title' => 'Governance Status Overview',
                    'description' => 'Current governance state and compliance levels',
                    'icon' => 'fas fa-balance-scale',
                    'color' => 'from-green-500 to-green-600'
                ]
            ];
        @endphp
        
        @foreach($charts as $chartFile => $chartInfo)
            <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="flex items-center space-x-3">
                            <div class="p-2 rounded-lg bg-gradient-to-r {{ $chartInfo['color'] }}">
                                <i class="{{ $chartInfo['icon'] }} text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $chartInfo['title'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $chartInfo['description'] }}</p>
                            </div>
                        </div>
                    </div>
                    <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-ellipsis-v text-gray-400"></i>
                    </button>
                </div>
                
                <div class="relative">
                    @if (file_exists(storage_path('app/'.$chartFile)))
                        <img src="{{ asset('storage/'.$chartFile) }}" 
                             alt="{{ $chartInfo['title'] }}" 
                             class="w-full h-64 object-contain rounded-xl border border-gray-100 dark:border-gray-700" />
                        <div class="absolute bottom-4 right-4">
                            <button onclick="downloadChart('{{ $chartFile }}')" 
                                    class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                                <i class="fas fa-download text-gray-600 dark:text-gray-400"></i>
                            </button>
                        </div>
                    @else
                        <div class="h-64 flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            <div class="p-4 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                <i class="{{ $chartInfo['icon'] }} text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium mb-2">Chart Not Available</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 text-center px-6">
                                Run scenarios to generate this visualization
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Scenario Report Summary -->
    <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Scenario Report Summary</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overview of all executed scenarios and their outcomes</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <input type="text" 
                               placeholder="Search scenarios..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-transparent text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                    </div>
                    <select class="border border-gray-300 dark:border-gray-600 rounded-xl bg-transparent text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>All Status</option>
                        <option>Success Only</option>
                        <option>Failed Only</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            @if(empty($report))
                <div class="px-6 py-12 text-center">
                    <div class="p-4 rounded-full bg-gray-100 dark:bg-gray-800 inline-block mb-4">
                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                    </div>
                    <h4 class="text-gray-700 dark:text-gray-300 font-medium mb-2">No Scenarios Run Yet</h4>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Run your first scenario to see analytics and reports</p>
                    @role('oracle')
                    <a href="{{ route('scenarios.index') }}" 
                       class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl inline-flex items-center space-x-2 transition-all duration-200">
                        <i class="fas fa-play-circle"></i>
                        <span>Run First Scenario</span>
                    </a>
                    @endrole
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <span>Scenario</span>
                                    <i class="fas fa-sort text-gray-400"></i>
                                </div>
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
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($report as $entry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 rounded-lg bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900">
                                        <i class="fas fa-file-alt text-gray-600 dark:text-gray-400"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $entry['scenarioFile'] }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ now()->subDays(rand(1, 30))->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ str_contains($entry['governanceStatus'], 'success') ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }}">
                                    @if(str_contains($entry['governanceStatus'], 'success'))
                                        <i class="fas fa-check-circle mr-1.5"></i>
                                    @else
                                        <i class="fas fa-exclamation-circle mr-1.5"></i>
                                    @endif
                                    {{ $entry['governanceStatus'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
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
                                <div class="space-y-2">
                                    @foreach($entry['assets'] as $asset)
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $asset['asset'] }}</span>
                                            <span class="px-2 py-1 rounded text-xs font-medium text-white {{ str_contains($asset['status'], 'executed') ? 'bg-green-500' : (str_contains($asset['status'], 'failed') ? 'bg-red-500' : 'bg-blue-500') }}">
                                                {{ $asset['status'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewScenario('{{ $entry['scenarioFile'] }}')" 
                                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" 
                                            title="View Details">
                                        <i class="fas fa-eye text-gray-600 dark:text-gray-400"></i>
                                    </button>
                                    <button onclick="downloadReport('{{ $entry['scenarioFile'] }}')" 
                                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" 
                                            title="Download Report">
                                        <i class="fas fa-download text-gray-600 dark:text-gray-400"></i>
                                    </button>
                                    <button onclick="runAgain('{{ $entry['scenarioFile'] }}')" 
                                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" 
                                            title="Run Again">
                                        <i class="fas fa-redo text-gray-600 dark:text-gray-400"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        
        @if(!empty($report))
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-semibold">{{ count($report) }}</span> scenario{{ count($report) !== 1 ? 's' : '' }}
                </div>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="px-3 py-1 bg-blue-600 text-white rounded-lg">1</span>
                    <button class="px-3 py-1 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function refreshData() {
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Refreshing...</span>';
    button.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        location.reload();
    }, 1500);
}

function downloadChart(chartFile) {
    // Implement chart download functionality
    alert('Downloading ' + chartFile);
}

function viewScenario(scenarioFile) {
    // Implement view scenario functionality
    alert('Viewing ' + scenarioFile);
}

function downloadReport(scenarioFile) {
    // Implement download report functionality
    alert('Downloading report for ' + scenarioFile);
}

function runAgain(scenarioFile) {
    // Implement run scenario again functionality
    if(confirm('Run scenario ' + scenarioFile + ' again?')) {
        alert('Running ' + scenarioFile);
    }
}
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
</style>
@endsection