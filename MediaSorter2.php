<?php

/**
 * Description of MediaSorter2
 *
 * @author josselin
 */
class MediaSorter2
{

    private $input = null;
    private $output = null;

    public function __construct($input = null, $output = null)
    {
        if (empty($input)) {
            throw new Exception('"Input" option is empty');
        } elseif (!is_dir($input)) {
            throw new Exception('"Input" directory is not valid');
        } else {
            $this->input = $input;
        }

        if (empty($output)) {
            throw new Exception('"Output" option is empty');
        } else {
            $this->output = $output;
        }
    }

    public function sort()
    {
        $this->browse($this->input);
    }

    private function browse($directory)
    {
        $handle = opendir($directory);

        if ($handle) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, array('.', '..'))) {
                    if (is_file($directory . DIRECTORY_SEPARATOR . $entry)) {
                        $this->analyse($directory . DIRECTORY_SEPARATOR . $entry);
                    }

                    if (is_dir($directory . DIRECTORY_SEPARATOR . $entry)) {
                        $this->browse($directory . DIRECTORY_SEPARATOR . $entry);
                    }
                }
            }

            closedir($handle);
        } else {
            throw new Exception('Cannot open directory "' . $directory . '"');
        }
    }

    private function analyse($file)
    {
        echo '===> ' . $file . "\n";
        $pathinfo = pathinfo($file);
        print_r($pathinfo);
    }

}
