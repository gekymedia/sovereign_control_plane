import './bootstrap';
import 'alpinejs';

// Chart.js is loaded via npm when building assets. Importing it here ensures
// that the bundle includes it so charts can be rendered in Livewire components
// or within Blade templates using inline scripts.
import { Chart, registerables } from 'chart.js/auto';
Chart.register(...registerables);

// You can initialise Alpine.js components here if needed. Currently the
// governance console uses standard Blade templates and Livewire components
// without additional JS initialisation.