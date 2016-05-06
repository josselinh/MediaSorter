<?php

require_once './CliInteractive.php';
require_once './MediaSorter.php';

$input = (!empty($argv[1]) ? $argv[1] : null);
$output = (!empty($argv[2]) ? $argv[2] : null);

$CliInteractive = new CliInteractive();
$CliInteractive->display('*** Media Sorter Script ***');

while (!is_file($input)) {
    $input = $CliInteractive->ask('Input?');
}

while (empty($output)) {
    $output = $CliInteractive->ask('Output?');
}

$MediaSorter = new MediaSorter();

/* Read file */
$handle = fopen($input, 'r');
$datetimes = array();

if (false !== $handle) {
    while (false !== ($entry = fgetcsv($handle, 0, ';'))) {
        if (is_file($entry[0])) {
            $choice = $entry[5];

            if (empty($choice) || !in_array($choice, array(0, 1, 2, 3, 4))) {
                $choice = 0;
            }

            if (0 !== $choice) {
                $datetimes[] = array(
                    'file' => $entry[0],
                    'datetime' => strtotime($entry[$choice])
                );
            }
        }
    }

    fclose($handle);
}

$MediaSorter->execute($datetimes, $output);