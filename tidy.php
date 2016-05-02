<?php

require_once './Cli.php';

$toSortDirectory = $argv[1];
$sortedDirectory = $argv[2];

$Cli = new Cli();

if (!is_dir($toSortDirectory)) {
    
}

if (!is_dir($sortedDirectory)) {
    $Cli->out(sprintf('The directory "%s" does not exist', $sortedDirectory));
    $Cli->out("Do you want to create it?");

    if ($Cli->in('Create?', array('yes', 'no'), 'yes') == 'yes') {
        if (mkdir($sortedDirectory, 0777, true)) {
            $Cli->out('Created!');
        } else {
            $Cli->err('Failed');
            exit;
        }
    }
}

if ($handleToSortDirectory = opendir($toSortDirectory)) {
    while (false !== ($entry = readdir($handleToSortDirectory))) {
        $pathInfo = pathinfo($toSortDirectory . $entry);
        $stat = stat($toSortDirectory . $entry);

        if (!is_dir($sortedDirectory . DIRECTORY_SEPARATOR . date('Y', $stat['mtime']) . DIRECTORY_SEPARATOR . date('m', $stat['mtime']) . DIRECTORY_SEPARATOR . date('d', $stat['mtime']) . DIRECTORY_SEPARATOR)) {
            mkdir($sortedDirectory . DIRECTORY_SEPARATOR . date('Y', $stat['mtime']) . DIRECTORY_SEPARATOR . date('m', $stat['mtime']) . DIRECTORY_SEPARATOR . date('d', $stat['mtime']) . DIRECTORY_SEPARATOR, 0777, true);
        }


        if (in_array($pathInfo['extension'], array('mp4', 'jpg'))) {
            $mtime = date('Y-m-d H:i:s', $stat['mtime']);
            $Cli->out($mtime);



            //copy($toSortDirectory.$entry, $sortedDirectory.DIRECTORY_SEPARATOR .sprintf('VID_%s', date('Ymd_His', $stat['mtime'])));
            //rename($toSortDirectory . $entry, $sortedDirectory . DIRECTORY_SEPARATOR . date('Y', $stat['mtime']) . DIRECTORY_SEPARATOR . date('m', $stat['mtime']) . DIRECTORY_SEPARATOR . date('d', $stat['mtime']) . DIRECTORY_SEPARATOR . sprintf('VID_%s', date('Ymd_His', $stat['mtime'])));
            //$Cli->out($sortedDirectory.DIRECTORY_SEPARATOR . date('Y', $stat['mtime']) . DIRECTORY_SEPARATOR .  date('m', $stat['mtime']).DIRECTORY_SEPARATOR.date('d', $stat['mtime']).DIRECTORY_SEPARATOR.sprintf('VID_%s', date('Ymd_His', $stat['mtime'])));
        }
    }
}