<?php
// Script to check what's available on production server
// Upload to production: public/check_production.php
// Access: https://your-domain.com/check_production.php

echo "<pre>";
echo "=== PHP Version ===\n";
echo PHP_VERSION . "\n\n";

echo "=== System Commands Available ===\n";
$commands = ['gs', 'ghostscript', 'qpdf', 'pdftk', 'convert', 'magick'];
foreach ($commands as $cmd) {
    exec("which $cmd 2>&1", $output, $return);
    echo "$cmd: " . ($return === 0 ? "✅ AVAILABLE at " . implode('', $output) : "❌ Not found") . "\n";
    unset($output);
}

echo "\n=== PHP Extensions ===\n";
$extensions = ['gd', 'imagick', 'gmagick', 'zlib', 'zip'];
foreach ($extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "✅ AVAILABLE" : "❌ Not loaded") . "\n";
}

echo "\n=== Shell Execution ===\n";
echo "exec() enabled: " . (function_exists('exec') ? "✅ YES" : "❌ NO") . "\n";
echo "shell_exec() enabled: " . (function_exists('shell_exec') ? "✅ YES" : "❌ NO") . "\n";
echo "system() enabled: " . (function_exists('system') ? "✅ YES" : "❌ NO") . "\n";

echo "\n=== Composer Packages ===\n";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    echo "setasign/fpdi: " . (class_exists('setasign\Fpdi\Tcpdf\Fpdi') ? "✅ INSTALLED" : "❌ Not found") . "\n";
    echo "tecnickcom/tcpdf: " . (class_exists('TCPDF') ? "✅ INSTALLED" : "❌ Not found") . "\n";
} else {
    echo "Vendor autoload not found\n";
}

echo "</pre>";

echo "\n\n<strong style='color: red;'>⚠️ DELETE THIS FILE AFTER CHECKING!</strong>\n";
