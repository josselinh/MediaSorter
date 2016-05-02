<?php

/**
 * Description of MediaSorter
 *
 * @author josselin
 */
class MediaSorter
{

    public function browseDirectory($input, $output, $media)
    {
        if ($handle = opendir($input)) {
            while (false !== ($file = readdir($handle))) {
                if (is_file($input . DIRECTORY_SEPARATOR . $file)) {
                    $pathInfo = pathinfo($input . DIRECTORY_SEPARATOR . $file);

                    if ($media == $pathInfo['extension']) {
                        $this->analyse($media, $input . DIRECTORY_SEPARATOR . $file);
                    }
                }
            }

            closedir($handle);
        } else {
            throw new Exception('Could not open directory');
        }
    }

    public function analyse($media, $file)
    {
        switch ($media) {
            case 'jpg':
                $this->analyseJpg($file);
                break;
        }
    }

    public function analyseJpg($file)
    {
        $exif = exif_read_data($file);
        
        if (false === $exif) {
            echo 'Error !!!'."\n";
        }
        /*
            if (!empty($exif['DateTimeOriginal'])) {
                echo date('d-m-Y H:i:s', strtotime($exif['DateTimeOriginal'])) . "\n";
            }
        } else {
            echo 'error'."\n";
        }*/


        //echo date('Y-m-d H:i:s', $exif['FileDateTime'])."\n";
        //echo $exif['DateTime']."\n";
        //echo $exif['DateTimeOriginal']."\n";
        //echo "\n";
    }

}
