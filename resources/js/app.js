import './bootstrap';

import Alpine from 'alpinejs';

// Import Tabulator
import { TabulatorFull as Tabulator } from 'tabulator-tables';

// Make Tabulator globally available
window.Tabulator = Tabulator;

window.Alpine = Alpine;

Alpine.start();
