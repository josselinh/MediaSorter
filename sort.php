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
    'media' => null,
    'method' => null);

/* Retrieve options */
for ($index = 1; $index < $argc; $index++) {
    list($option, $value) = explode(':', $argv[$index]);
    $options[$option] = $value;
}

/* Call methods & sort */
try {
    $MediaSorter->setInput($options['input']);
    $MediaSorter->setOutput($options['output']);
    $MediaSorter->setMedia($options['media']);
    $MediaSorter->setMethod($options['method']);

    $MediaSorter->sort();
} catch (Exception $e) {
    $CliInteractive->error('/!\ ' . $e->getMessage());
}
