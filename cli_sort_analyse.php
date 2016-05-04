<?php

/* Requires */
require_once './CliInteractive.php';
require_once './MediaSorter2.php';

/* Start */
$CliInteractive = new CliInteractive();
$CliInteractive->display('*** Media Sorter Script ***');

/* Get input argument */
$input = (!empty($argv[1]) ? $argv[1] : null);

/* Ask the input argument if not passed by the script */
while (!is_dir($input)) {
    $input = $CliInteractive->ask('Input?');
}

/* Analyse input directory */
$MediaSorter = new MediaSorter2();

try {
    $datetimes = $MediaSorter->analyse($input);
} catch (Exception $e) {
    $CliInteractive->error($e->getMessage());
    exit(-1);
}

/* Create a CSV file */
$file = $input . DIRECTORY_SEPARATOR . 'analyse_' . date('Ymd_His') . '.csv';
$handle = fopen($file, 'w');

if (false !== $handle) {
    fputs($handle, 'File;Filename;Exif_datetimeoriginal;Exif_filedatetime;Modified;Choice' . "\n");

    foreach ($datetimes as $datetime) {
        fputs($handle, $datetime['file'] . ';' .
                date('Y-m-d H:i:s', $datetime['filename']) . ';' .
                date('Y-m-d H:i:s', $datetime['exif_datetimeoriginal']) . ';' .
                date('Y-m-d H:i:s', $datetime['exif_filedatetime']) . ';' .
                date('Y-m-d H:i:s', $datetime['modified']) . ';' .
                '' . "\n");
    }

    fclose($handle);

    $CliInteractive->display('File successfully generated');
    $CliInteractive->display($file);
}