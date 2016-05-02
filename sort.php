<?php

require_once './CliInteractive.php';
require_once './MediaSorter.php';

/* Objects */
$MediaSorter = new MediaSorter();
$CliInteractive = new CliInteractive();
$CliInteractive->display('** Media Sorter Script **', 2);

/* Available options */
$options = array(
    'input' => null,
    'output' => null,
    'media' => null);

/* Retrieve options */
for ($index = 1; $index < $argc; $index++) {
    list($option, $value) = explode(':', $argv[$index]);
    $options[$option] = $value;
}

/* Check options */
$continue = true;
if (empty($options['input']) || !is_dir($options['input'])) {
    $CliInteractive->display('/!\ Input directory option is empty or is not a directory');
    $continue = false;
}

if (empty($options['output'])) {
    $CliInteractive->display('/!\ Output directory option is empty');
    $continue = false;
} elseif (!is_dir($options['output'])) {
    $CliInteractive->display('/!\ Output directory does not exist, creating ... ', 0);
    
    if (mkdir($options['output'], 0777, true)) {
        $CliInteractive->display('Done!');
    } else {
        $CliInteractive->display('Failed!');
        $continue = false;
    }
}

/* Options validated */
if (false === $continue) {
    $CliInteractive->error('Aborted');
}

/* Browse input directory */
$MediaSorter->browseDirectory($options['input'], $options['output'], $options['media']);