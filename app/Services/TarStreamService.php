<?php

namespace App\Services;

class TarStreamService
{
    public static function streamDirectoryAsTar(string $dirPath, string $tarName)
    {
        $dirPath = rtrim($dirPath, '/');
        if (!is_dir($dirPath)) {
            abort(404, 'Backup directory not found');
        }

        return response()->streamDownload(function () use ($dirPath) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $relative = ltrim(str_replace($dirPath, '', $file->getPathname()), '/');
                $size = $file->isDir() ? 0 : filesize($file->getPathname());
                self::writeTarHeader($relative, $file->isDir(), $size, filemtime($file->getPathname()));
                if (!$file->isDir()) {
                    $fh = fopen($file->getPathname(), 'rb');
                    $bytes = 0;
                    while (!feof($fh)) {
                        $chunk = fread($fh, 8192);
                        $bytes += strlen($chunk);
                        echo $chunk;
                    }
                    fclose($fh);
                    // Pad to 512-byte blocks
                    $pad = ($bytes % 512) ? (512 - ($bytes % 512)) : 0;
                    if ($pad > 0) echo str_repeat("\0", $pad);
                }
            }
            // Two zero blocks to end tar
            echo str_repeat("\0", 1024);
        }, $tarName, [
            'Content-Type' => 'application/x-tar'
        ]);
    }

    protected static function writeTarHeader(string $name, bool $isDir, int $size, int $mtime): void
    {
        $name = substr($name, 0, 100);
        $mode = sprintf('%07o', $isDir ? 0755 : 0644);
        $uid = sprintf('%07o', 0);
        $gid = sprintf('%07o', 0);
        $sizeOct = sprintf('%011o', $size);
        $mtimeOct = sprintf('%011o', $mtime);
        $chksum = str_repeat(' ', 8);
        $typeflag = $isDir ? '5' : '0';
        $linkname = str_repeat("\0", 100);
        $magic = 'ustar' . "\0";  // ustar with null terminator
        $version = '00';
        $uname = str_pad('user', 32, "\0");
        $gname = str_pad('group', 32, "\0");
        $devmajor = str_repeat("\0", 8);
        $devminor = str_repeat("\0", 8);
        $prefix = str_repeat("\0", 155);

        $header = str_pad($name, 100, "\0")
            . $mode
            . $uid
            . $gid
            . $sizeOct
            . $mtimeOct
            . $chksum
            . $typeflag
            . $linkname
            . $magic
            . $version
            . $uname
            . $gname
            . $devmajor
            . $devminor
            . $prefix
            . str_repeat("\0", 12);

        // Calculate checksum
        $sum = 0;
        for ($i = 0, $len = strlen($header); $i < $len; $i++) {
            $sum += ord($header[$i]);
        }
        $checksum = sprintf('%06o\0 ', $sum);
        $header = substr($header, 0, 148) . $checksum . substr($header, 156);

        echo $header;
    }
}
