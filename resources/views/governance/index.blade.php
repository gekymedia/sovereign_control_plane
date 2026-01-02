@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Governance Panel</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure system parameters and protocol settings</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="resetToDefaults()" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 flex items-center space-x-2">
                <i class="fas fa-undo text-gray-600 dark:text-gray-400"></i>
                <span class="text-gray-700 dark:text-gray-300">Reset Defaults</span>
            </button>
            <button type="button" onclick="validateAndSubmit()" 
                    class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl flex items-center space-x-2 transition-all duration-200 shadow-md hover:shadow-lg">
                <i class="fas fa-save"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('status'))
    <div class="p-4 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 animate-slide-down">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
            </div>
            <div class="flex-1">
                <p class="font-medium text-green-800 dark:text-green-300">Settings Updated</p>
                <p class="text-sm text-green-700 dark:text-green-400">{{ session('status') }}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" 
                    class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Main Form -->
    <form method="POST" action="{{ route('governance.update') }}" id="governanceForm" class="space-y-8">
        @csrf
        
        <!-- Overlay Configuration Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <div class="p-3 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600">
                    <i class="fas fa-layer-group text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Overlay Configuration</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Configure overlay tier and oracle settings</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Overlay Tier -->
                <div class="space-y-2">
                    <label for="overlay_tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center justify-between">
                            <span>Overlay Tier</span>
                            <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Required</span>
                        </div>
                    </label>
                    <div class="relative">
                        <select name="overlay_tier" id="overlay_tier" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none">
                            @php $current = $settings->overlay_tier ?? 'none'; @endphp
                            <option value="none" @selected($current=='none')>None</option>
                            <option value="ChaoticTier13" @selected($current=='ChaoticTier13')>ChaoticTier13</option>
                            <option value="SovereignTierX" @selected($current=='SovereignTierX')>SovereignTierX</option>
                        </select>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Select the overlay tier for protocol operations</p>
                </div>

                <!-- Expected Chainlink Rate -->
                <div class="space-y-2">
                    <label for="expected_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center space-x-2">
                            <span>Expected Chainlink Rate</span>
                            <div class="flex items-center text-xs text-amber-600 dark:text-amber-400">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span>Advanced</span>
                            </div>
                        </div>
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" name="expected_rate" id="expected_rate" 
                               value="{{ old('expected_rate', $settings->expected_rate) }}" 
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Enter expected rate...">
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Expected rate for Chainlink oracle feed</p>
                </div>

                <!-- Max Oracle Delay -->
                <div class="space-y-2">
                    <label for="max_oracle_delay" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center justify-between">
                            <span>Max Oracle Delay</span>
                            @if(($settings->max_oracle_delay ?? 0) > 60)
                            <span class="text-xs text-red-600 dark:text-red-400 font-medium">Warning: High Delay</span>
                            @endif
                        </div>
                    </label>
                    <div class="relative">
                        <input type="number" name="max_oracle_delay" id="max_oracle_delay" 
                               value="{{ old('max_oracle_delay', $settings->max_oracle_delay) }}" 
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Seconds...">
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-stopwatch"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Maximum allowed delay for oracle updates (seconds)</p>
                </div>
            </div>
        </div>

        <!-- DeFi Protocol Configuration Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <div class="p-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600">
                    <i class="fas fa-exchange-alt text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">DeFi Protocol Settings</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Configure Uniswap and Curve parameters</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Uniswap Fee Tier -->
                <div class="space-y-2">
                    <label for="uni_fee_tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center space-x-2">
                            <span>Uniswap Fee Tier</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">bps</span>
                        </div>
                    </label>
                    <div class="relative">
                        <input type="number" name="uni_fee_tier" id="uni_fee_tier" 
                               value="{{ old('uni_fee_tier', $settings->uni_fee_tier ?? '3000') }}" 
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="Basis points...">
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-tag"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Fee tier for Uniswap v3 pools (3000 = 0.3%)</p>
                </div>

                <!-- Curve Configuration -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="use_curve" id="use_curve" value="1" 
                                       class="sr-only peer" @checked($settings->use_curve)>
                                <div class="w-12 h-6 bg-gray-300 dark:bg-gray-600 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-blue-500 peer-checked:to-blue-600"></div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Curve Stabilisation</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Use Curve for stable asset swaps</p>
                            </div>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Curve Index In -->
                        <div class="space-y-2">
                            <label for="curve_index_in" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Curve Index In
                            </label>
                            <input type="number" name="curve_index_in" id="curve_index_in" 
                                   value="{{ old('curve_index_in', $settings->curve_index_in ?? '1') }}" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                   placeholder="Input index...">
                        </div>

                        <!-- Curve Index Out -->
                        <div class="space-y-2">
                            <label for="curve_index_out" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Curve Index Out
                            </label>
                            <input type="number" name="curve_index_out" id="curve_index_out" 
                                   value="{{ old('curve_index_out', $settings->curve_index_out ?? '0') }}" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                   placeholder="Output index...">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Index positions in the Curve pool for input/output assets</p>
                </div>
            </div>
        </div>

        <!-- Wallet Configuration Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center space-x-3 mb-6">
                <div class="p-3 rounded-xl bg-gradient-to-r from-green-500 to-green-600">
                    <i class="fas fa-wallet text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Wallet Configuration</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Configure asset and profit distribution addresses</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Loan Asset -->
                <div class="space-y-2">
                    <label for="loan_asset" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center justify-between">
                            <span>Loan Asset Address</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">0x...</span>
                        </div>
                    </label>
                    <div class="relative">
                        <input type="text" name="loan_asset" id="loan_asset" 
                               value="{{ old('loan_asset', $settings->loan_asset) }}" 
                               class="w-full px-4 py-3 pl-10 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="0x...">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">ERC20 token address used for lending operations</p>
                </div>

                <!-- Primary Profit Wallet -->
                <div class="space-y-2">
                    <label for="profit_wallet" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center justify-between">
                            <span>Primary Profit Wallet</span>
                            <div class="flex items-center text-xs text-green-600 dark:text-green-400">
                                <i class="fas fa-check-circle mr-1"></i>
                                <span>Verified</span>
                            </div>
                        </div>
                    </label>
                    <div class="relative">
                        <input type="text" name="profit_wallet" id="profit_wallet" 
                               value="{{ old('profit_wallet', $settings->profit_wallet) }}" 
                               class="w-full px-4 py-3 pl-10 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="0x...">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-university"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Main wallet for profit distribution</p>
                </div>

                <!-- Fallback Wallet -->
                <div class="space-y-2">
                    <label for="fallback_wallet" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center justify-between">
                            <span>Fallback Wallet</span>
                            <div class="flex items-center text-xs text-amber-600 dark:text-amber-400">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <span>Emergency</span>
                            </div>
                        </div>
                    </label>
                    <div class="relative">
                        <input type="text" name="fallback_wallet" id="fallback_wallet" 
                               value="{{ old('fallback_wallet', $settings->fallback_wallet) }}" 
                               class="w-full px-4 py-3 pl-10 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                               placeholder="0x...">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-life-ring"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Emergency wallet for fund recovery</p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-info-circle mr-2"></i>
                Changes take effect immediately after saving
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" onclick="window.history.back()" 
                        class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-8 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl flex items-center space-x-2 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-save"></i>
                    <span class="font-medium">Save All Changes</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function validateAndSubmit() {
    const form = document.getElementById('governanceForm');
    let isValid = true;
    const requiredFields = ['overlay_tier', 'expected_rate', 'max_oracle_delay'];
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            field.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            field.classList.remove('border-gray-300', 'dark:border-gray-600');
            isValid = false;
            
            // Add error message
            if (!field.nextElementSibling?.classList.contains('error-message')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-sm text-red-600 dark:text-red-400 mt-1';
                errorDiv.textContent = 'This field is required';
                field.parentElement.appendChild(errorDiv);
            }
        } else {
            field.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            field.classList.add('border-gray-300', 'dark:border-gray-600');
            
            // Remove error message
            const errorMsg = field.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        }
    });
    
    if (isValid) {
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Saving...</span>';
        submitBtn.disabled = true;
        
        // Submit form
        form.submit();
    } else {
        // Scroll to first error
        const firstError = document.querySelector('.border-red-500');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
        
        // Show error notification
        showNotification('Please fill all required fields', 'error');
    }
}

function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to defaults?')) {
        const defaults = {
            overlay_tier: 'none',
            expected_rate: '',
            max_oracle_delay: '',
            uni_fee_tier: '3000',
            curve_index_in: '1',
            curve_index_out: '0',
            loan_asset: '',
            profit_wallet: '',
            fallback_wallet: ''
        };
        
        Object.keys(defaults).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = false;
                } else {
                    element.value = defaults[key];
                }
            }
        });
        
        showNotification('Settings reset to defaults', 'success');
    }
}

function showNotification(message, type = 'success') {
    const container = document.createElement('div');
    container.className = `fixed top-6 right-6 z-50 animate-slide-down ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white rounded-xl p-4 shadow-lg max-w-sm`;
    
    const content = `
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            </div>
            <div class="flex-1">
                <p class="font-medium">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white/80 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.innerHTML = content;
    document.body.appendChild(container);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (container.parentElement) {
            container.remove();
        }
    }, 5000);
}

// Real-time validation
document.querySelectorAll('input, select').forEach(element => {
    element.addEventListener('input', function() {
        if (this.classList.contains('border-red-500')) {
            this.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            this.classList.add('border-gray-300', 'dark:border-gray-600');
            
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        }
    });
});

// Toggle Curve fields based on checkbox
const curveCheckbox = document.getElementById('use_curve');
const curveFields = ['curve_index_in', 'curve_index_out'];

function toggleCurveFields() {
    const isEnabled = curveCheckbox.checked;
    curveFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.disabled = !isEnabled;
            field.style.opacity = isEnabled ? '1' : '0.5';
        }
    });
}

curveCheckbox.addEventListener('change', toggleCurveFields);
// Initialize on load
toggleCurveFields();
</script>

<style>
.animate-slide-down {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

input:focus, select:focus {
    outline: none;
}

/* Custom scrollbar for select */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* Smooth transitions for form elements */
input, select {
    transition: all 0.2s ease-in-out;
}

/* Hover effects for cards */
.bg-gradient-to-br {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.bg-gradient-to-br:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>
@endsection