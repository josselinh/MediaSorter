<?php

require_once './CliInteractive.php';
require_once './MediaSorter2.php';

$input = (!empty($argv[1]) ? $argv[1] : null);
$output = (!empty($argv[2]) ? $argv[2] : null);

$CliInteractive = new CliInteractive();
$CliInteractive->display('*** Media Sorter Script ***');

while (!is_dir($input)) {
    $input = $CliInteractive->ask('Input?');
}

while (empty($output)) {
    $output = $CliInteractive->ask('Output?');
}

$MediaSorter = new MediaSorter2($input, $output);
$MediaSorter->sort();