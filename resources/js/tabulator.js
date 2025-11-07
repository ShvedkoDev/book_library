// Import Tabulator CSS
import 'tabulator-tables/dist/css/tabulator.min.css';

// Import Tabulator
import { TabulatorFull as Tabulator } from 'tabulator-tables';

// Import XLSX for Excel export
import * as XLSX from 'xlsx';

// Make Tabulator globally available
window.Tabulator = Tabulator;

// Make XLSX globally available
window.XLSX = XLSX;

console.log('Tabulator and XLSX loaded successfully');
