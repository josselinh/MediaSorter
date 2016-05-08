<?php

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Description of MediaSorter
 *
 * @author josselin
 */
class MediaSorter
{

    /**
     *
     * @var type 
     */
    private $masks = array(
        '(\d{4})/(\d{2})/(\d{2})/(.*).(jpg|dng|mp4)' => array('Y' => 1, 'm' => 2, 'd' => 3),
        '(IMG|VID)_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})((_|~).*)?.(jpg|dng|mp4)' => array('Y' => 2, 'm' => 3, 'd' => 4, 'H' => 5, 'i' => 6, 's' => 7),
        '(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})(_.*)?.(jpg|dng|mp4)' => array('Y' => 1, 'm' => 2, 'd' => 3, 'H' => 4, 'i' => 5, 's' => 6),
        '(\d{4})-(\d{2})-(\d{2})_(\d{2})-(\d{2})-(\d{2})_(.*).jpg' => array('Y' => 1, 'm' => 2, 'd' => 3, 'H' => 4, 'i' => 5, 's' => 6),
    );

    /**
     *
     * @var type 
     */
    private $mkdir_mode = 0777;

    /**
     *
     * @var type 
     */
    private $file_name_prefix = array('jpg' => 'IMG_', 'dng' => 'IMG_', 'mp4' => 'VID_');

    /**
     *
     * @var type 
     */
    private $file_name_suffix = array('jpg' => '', 'dng' => '', 'mp4' => '');

    /**
     *
     * @var type 
     */
    private $errors = array();

    /**
     * 
     * @param type $input
     * @return type
     * @throws Exception
     */
    public function analyse($input = null)
    {
        if (empty($input)) {
            throw new Exception('"Input" option is empty');
        } elseif (!is_dir($input)) {
            throw new Exception('"Input" directory is not valid');
        }

        return $this->browse($input);
    }

    /**
     * 
     * @param type $directory
     * @throws Exception
     */
    private function browse($directory = null, &$datetimes = array())
    {
        $handle = opendir($directory);

        if ($handle) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, array('.', '..'))) {
                    if (is_file($directory . DIRECTORY_SEPARATOR . $entry)) {
                        if (false !== ($dates = $this->retrieveDates($directory . DIRECTORY_SEPARATOR . $entry))) {
                            $datetimes[] = $dates;
                        }
                    }

                    if (is_dir($directory . DIRECTORY_SEPARATOR . $entry)) {
                        $this->browse($directory . DIRECTORY_SEPARATOR . $entry, $datetimes);
                    }
                }
            }

            closedir($handle);
        } else {
            throw new Exception('Cannot open directory "' . $directory . '"');
        }

        return $datetimes;
    }

    /**
     * 
     * @param type $file
     * @return type
     */
    private function retrieveDates($file = null)
    {
        $found = false;

        $dates = array(
            'file' => $file,
            'filename' => null,
            'exif_datetimeoriginal' => null,
            'exif_filedatetime' => null,
            'modified' => filemtime($file)
        );

        foreach ($this->masks as $pattern => $orders) {
            if (preg_match('#' . $pattern . '#i', $file, $matches)) {
                $found = true;

                $datetimeValues = array(
                    'Y' => date('Y'),
                    'm' => date('m'),
                    'd' => date('d'),
                    'H' => date('H'),
                    'i' => date('i'),
                    's' => date('s')
                );

                //echo implode("\t", $matches) . "\n";

                foreach ($orders as $format => $num) {
                    $datetimeValues[$format] = $matches[$num];
                }

                /* Try filename */
                $dates['filename'] = mktime($datetimeValues['H'], $datetimeValues['i'], $datetimeValues['s'], $datetimeValues['m'], $datetimeValues['d'], $datetimeValues['Y']);

                /* Try EXIF */
                $exif = @read_exif_data($file);

                if (false !== $exif) {
                    if (!empty($exif['DateTimeOriginal'])) {
                        $dates['exif_datetimeoriginal'] = strtotime($exif['DateTimeOriginal']);
                    }

                    if (!empty($exif['FileDateTime'])) {
                        $dates['exif_filedatetime'] = $exif['FileDateTime'];
                    }
                }

                break;
            }
        }

        return ($found ? $dates : false);
    }

    /**
     * 
     * @param type $datetimes
     * @param type $output
     * @throws Exception
     */
    public function execute($datetimes = array(), $output = null)
    {
        /* Check params */
        /* -> Datetimes empty */
        if (empty($datetimes)) {
            throw new Exception('Datetimes variable is empty');
        }

        /* -> Output directory empty */
        if (empty($output)) {
            throw new Exception('Output directory option is empty');
        }

        /* -> Output directory creation */
        if (!is_dir($output)) {
            if (!mkdir($output, $this->mkdir_mode, true)) {
                throw new Exception('Could not create output directory');
            }
        }

        /* Browse datetimes */
        foreach ($datetimes as $datetime) {
            $pathinfo = pathinfo($datetime['file']);
            $fileExt = $pathinfo['extension'];

            /* New file name */
            $newFileNameWithoutExt = (!empty($this->file_name_prefix[$fileExt]) ? $this->file_name_prefix[$fileExt] : null) .
                    date('Ymd_His', $datetime['datetime']) .
                    (!empty($this->file_name_suffix[$fileExt]) ? $this->file_name_suffix[$fileExt] : null);

            /* New directory name */
            $newDirectoryName = $output . DS .
                    date('Y', $datetime['datetime']) . DS .
                    date('m', $datetime['datetime']) . DS .
                    date('d', $datetime['datetime']) . DS;

            /* Check output file directory */
            if ($this->outputFileDirectory($newDirectoryName)) {
                /* File name double */
                if (is_file($newDirectoryName . $newFileNameWithoutExt . '.' . $fileExt)) {
                    $i = 1;

                    while (is_file($newDirectoryName . $newFileNameWithoutExt . '_' . $i . '.' . $fileExt)) {
                        $i = $i + 1;
                    }

                    $newFileNameWithoutExt = $newFileNameWithoutExt . '_' . $i;
                }

                /* Move file */
                if (rename($datetime['file'], $newDirectoryName . $newFileNameWithoutExt . '.' . $fileExt)) {
                    if (!touch($newDirectoryName . $newFileNameWithoutExt . '.' . $fileExt, $datetime['datetime'])) {
                        $this->errors[] = 'Could not change modified date for ' . $newDirectoryName . $newFileNameWithoutExt . '.' . $fileExt;
                    }
                } else {
                    $this->errors[] = 'Could not move ' . $newDirectoryName . $newFileNameWithoutExt . '.' . $fileExt;
                }
            }
        }
    }

    /**
     * 
     * @param type $directory
     * @return boolean
     */
    private function outputFileDirectory($directory = null)
    {
        $continue = true;

        if (!is_dir($directory)) {
            if (!mkdir($directory, $this->mkdir_mode, true)) {
                $continue = false;
            }
        }

        return $continue;
    }

    public function getErrors()
    {
        return $this->errors;
    }

}
