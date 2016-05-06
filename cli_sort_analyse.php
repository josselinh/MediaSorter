<?php

/* Requires */
require_once './CliInteractive.php';
require_once './MediaSorter.php';

/* Start */
$CliInteractive = new CliInteractive();
$CliInteractive->display('*** Media Sorter Script ***');

/* Get input argument */
$input = (!empty($argv[1]) ? $argv[1] : null);
$output = (!empty($argv[2]) ? $argv[2] : 'print');

/* Ask the input argument if not passed by the script */
while (!is_dir($input)) {
    $input = $CliInteractive->ask('Input?');
}

/* Check output argument */
if (!in_array($output, array('print', 'file', 'debug'))) {
    $output = $CliInteractive->ask('Output?', array('print', 'file'), 'print');
}

/* Analyse input directory */
$MediaSorter = new MediaSorter();

try {
    $datetimes = $MediaSorter->analyse($input);
} catch (Exception $e) {
    $CliInteractive->error($e->getMessage());
    exit(-1);
}

/* Output (user choice) */
/* Print */
if ('print' === $output) {
    foreach ($datetimes as $datetime) {
        if (!empty($datetime['filename'])) {
            $datetime['filename'] = date('Y-m-d H:i:s', $datetime['filename']);
        } else {
            $datetime['filemane'] = '                   ';
        }

        if (!empty($datetime['exif_datetimeoriginal'])) {
            $datetime['exif_datetimeoriginal'] = date('Y-m-d H:i:s', $datetime['exif_datetimeoriginal']);
        } else {
            $datetime['exif_datetimeoriginal'] = '                   ';
        }

        if (!empty($datetime['exif_filedatetime'])) {
            $datetime['exif_filedatetime'] = date('Y-m-d H:i:s', $datetime['exif_filedatetime']);
        } else {
            $datetime['exif_filedatetime'] = '                   ';
        }

        $datetime['modified'] = date('Y-m-d H:i:s', $datetime['modified']);

        $CliInteractive->display(implode("\t", $datetime));
    }

    $CliInteractive->display(null);
    $CliInteractive->display(count($datetimes) . ' element(s) found');
}

/* File */
if ('file' === $output) {
    $file = $input . DIRECTORY_SEPARATOR . 'analyse.csv';

    try {
        $handle = @fopen($file, 'w');

        if (false !== $handle) {
            if (!fputcsv($handle, array('File', 'Filename', 'Exif Datetime Original', 'Exif File Datetime', 'Modified', 'Choice'), ';')) {
                throw new Exception('Cannot write in the output file');
            }

            foreach ($datetimes as $datetime) {
                if (!empty($datetime['filename'])) {
                    $datetime['filename'] = date('Y-m-d H:i:s', $datetime['filename']);
                }

                if (!empty($datetime['exif_datetimeoriginal'])) {
                    $datetime['exif_datetimeoriginal'] = date('Y-m-d H:i:s', $datetime['exif_datetimeoriginal']);
                }

                if (!empty($datetime['exif_filedatetime'])) {
                    $datetime['exif_filedatetime'] = date('Y-m-d H:i:s', $datetime['exif_filedatetime']);
                }

                $datetime['modified'] = date('Y-m-d H:i:s', $datetime['modified']);
                $datetime['choice'] = '';

                if (!fputcsv($handle, $datetime, ';')) {
                    throw new Exception('Cannot write in the output file');
                }
            }

            fclose($handle);

            $CliInteractive->display('File successfully generated');
            $CliInteractive->display($file);
        } else {
            throw new Exception('Cannot create output file');
        }
    } catch (Exception $e) {
        $CliInteractive->error($e->getMessage());
        exit(-1);
    }
}