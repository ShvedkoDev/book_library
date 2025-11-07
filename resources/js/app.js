import './bootstrap';

import Alpine from 'alpinejs';

// Import Tabulator
import { TabulatorFull as Tabulator } from 'tabulator-tables';

// Import XLSX for Excel export
import * as XLSX from 'xlsx';

// Make Tabulator globally available
window.Tabulator = Tabulator;

// Make XLSX globally available
window.XLSX = XLSX;

window.Alpine = Alpine;

Alpine.start();
