<?php

/**
 * Description of MediaSorter2
 *
 * @author josselin
 */
class MediaSorter2
{

    /**
     *
     * @var type 
     */
    private $input = null;

    /**
     *
     * @var type 
     */
    private $output = null;

    /**
     *
     * @var type 
     */
    private $masks = array(
        '(\d{4})/(\d{2})/(\d{2})/(.*).jpg' => array('Y', 'm', 'd'),
        'IMG_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2}).jpg' => array('Y', 'm', 'd', 'H', 'i', 's'),
        'IMG_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})_(.*).dng' => array('Y', 'm', 'd', 'H', 'i', 's'),
        '(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2}).jpg' => array('Y', 'm', 'd', 'H', 'i', 's'),
        '(\d{4})-(\d{2})-(\d{2})_(\d{2})-(\d{2})-(\d{2})_(.*).jpg' => array('Y', 'm', 'd', 'H', 'i', 's'),
        'VID_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2}).mp4' => array('Y', 'm', 'd', 'H', 'i', 's')
    );

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
        } else {
            $this->input = $input;
        }

        return $this->browse($this->input);
    }

    /**
     * 
     * @param type $directory
     * @throws Exception
     */
    private function browse($directory = null, $datetimes = array())
    {
        $handle = opendir($directory);

        if ($handle) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, array('.', '..'))) {
                    if (is_file($directory . DIRECTORY_SEPARATOR . $entry)) {
                        $datetimes[] = $this->retrieveDates($directory . DIRECTORY_SEPARATOR . $entry);
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
        $datetime = array(
            'file' => $file,
            'filename' => null,
            'exif_datetimeoriginal' => null,
            'exif_filedatetime' => null,
            'modified' => null
        );
        
        echo $file."\n";

        foreach ($this->masks as $pattern => $orders) {
            if (preg_match('#' . $pattern . '#', $file, $matches)) {
                $datetimeValues = array(
                    'Y' => date('Y'),
                    'm' => date('m'),
                    'd' => date('d'),
                    'H' => date('H'),
                    'i' => date('i'),
                    's' => date('s'));

                foreach ($orders as $num => $format) {
                    $datetimeValues[$format] = $matches[$num + 1];
                }

                /* Try filename */
                $datetime['filename'] = mktime($datetimeValues['H'], $datetimeValues['i'], $datetimeValues['s'], $datetimeValues['m'], $datetimeValues['d'], $datetimeValues['Y']);

                /* Try EXIF */
                $exif = @read_exif_data($file);

                if (false !== $exif) {
                    if (!empty($exif['DateTimeOriginal'])) {
                        $datetime['exif_datetimeoriginal'] = strtotime($exif['DateTimeOriginal']);
                    }

                    if (!empty($exif['FileDateTime'])) {
                        $datetime['exif_filedatetime'] = $exif['FileDateTime'];
                    }
                }

                /* Try Last Modified Date */
                $datetime['modified'] = filemtime($file);
            }
        }

        //print_r($datetime);

        return $datetime;
    }

    /**
     * 
     * @param type $datetimes
     * @param type $output
     * @throws Exception
     */
    public function execute($datetimes = array(), $output = null)
    {
        if (empty($output)) {
            throw new Exception('"Output" option is empty');
        } else {
            $this->output = $output;
        }

        foreach ($datetimes as $datetime) {

            $pathinfo = pathinfo($datetime['file']);


            $newFile = date('Ymd_His', $datetime['datetime']);

            switch ($pathinfo['extension']) {
                case 'jpg':
                    $newFile = 'IMG_' . $newFile . '.jpg';
                    break;

                case 'dng':
                    $newFile = 'IMG_' . $newFile . '.dng';
                    break;

                case 'mp4':
                    $newFile = 'VID_' . $newFile . '.mp4';
                    break;

                default:
                    throw new Exception('What ???');
                    break;
            }

            $newDirectory = $output . DIRECTORY_SEPARATOR . date('Y', $datetime['datetime']) . DIRECTORY_SEPARATOR . date('m', $datetime['datetime']) . DIRECTORY_SEPARATOR . date('d', $datetime['datetime']) . DIRECTORY_SEPARATOR;

            if (!is_dir($newDirectory)) {
                if (!mkdir($newDirectory, 0777, true)) {
                    
                }
            }

            if (!is_file($newDirectory . $newFile)) {
                if (rename($datetime['file'], $newDirectory . $newFile)) {
                    touch($newDirectory . $newFile, $datetime['datetime']);
                }
            }
        }
    }

}
