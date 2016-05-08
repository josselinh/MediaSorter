<?php

/* Requires */
require_once './CliInteractive.php';
require_once './MediaSorter.php';

/* Start */
$CliInteractive = new CliInteractive();
$CliInteractive->display('*** Media Sorter Script ***');

/* Get input argument */
$input = (!empty($argv[1]) ? $argv[1] : null);
$output = (!empty($argv[2]) ? $argv[2] : null);

/* Ask the input argument if not passed by the script */
while (!is_dir($input)) {
    $input = $CliInteractive->ask('Input?');
}

/* Ask the output argument if not passed by the script */
while (empty($output)) {
    $output = $CliInteractive->ask('Output?');
}

/* Analyse input directory */
$MediaSorter = new MediaSorter();

try {
    $datetimes = $MediaSorter->analyse($input);
    
    /* Default choice 1 (filename) */
    if (!empty($datetimes)) {
        foreach ($datetimes as $key => $datetime) {
            $datetimes[$key]['datetime'] = $datetime['filename'];
        }
    }

    $MediaSorter->execute($datetimes, $output);
    
    $CliInteractive->display('Done!');
    $CliInteractive->display('Errors : '.count($MediaSorter->getErrors()));
} catch (Exception $e) {
    $CliInteractive->error($e->getMessage());
    exit(-1);
}
