<?php

/**
 * Description of MediaSorter
 *
 * @author josselin
 */
class MediaSorter
{

    const MEDIA = array('jpg', 'mp4');
    const METHOD = array('exif', 'lastModified', 'directory', 'name');

    private $input = null;
    private $output = null;
    private $media = null;
    private $method = null;
    private $prefix = array('jpg' => 'IMAGE_', 'mp4' => 'VIDEO_');

    /**
     * 
     * @param type $input
     * @param type $output
     * @param type $media
     * @param type $method
     */
    public function __construct($input = null, $output = null, $media = 'jpg', $method = 'exif')
    {
        if (!empty($input) && !empty($output)) {
            $this->setInput($input);
            $this->setOutput($output);
            $this->setMedia($media);
            $this->setMethod($method);
        }
    }

    /**
     * 
     * @param type $input
     * @throws Exception
     */
    public function setInput($input = null)
    {
        if (empty($input)) {
            throw new Exception('"Input" option is empty');
        } elseif (!is_dir($input)) {
            throw new Exception('"Input" option is not a valid directory');
        } else {
            $this->input = $input;
        }
    }

    /**
     * 
     * @param type $output
     * @throws Exception
     */
    public function setOutput($output = null)
    {
        if (empty($output)) {
            throw new Exception('"Output" option is empty');
        } elseif (!is_dir($output) && !mkdir($output, 0777, true)) {
            throw new Exception('The "output" directory cannot be created');
        } else {
            $this->output = $output;
        }
    }

    /**
     * 
     * @param type $media
     * @throws Exception
     */
    public function setMedia($media = 'jpg')
    {
        if (empty($media)) {
            throw new Exception('"Media" option is empty');
        } elseif (!in_array($media, self::MEDIA)) {
            throw new Exception('"Media" option is not a valid type');
        } else {
            $this->media = $media;
        }
    }

    /**
     * 
     * @param type $method
     * @throws Exception
     */
    public function setMethod($method = 'exif')
    {
        if (empty($method)) {
            throw new Exception('"Method" option is empty');
        } elseif (!in_array($method, self::METHOD)) {
            throw new Exception('"Method" option is not a valid method');
        } else {
            $this->method = $method;
        }
    }

    public function sort()
    {
        $handle = opendir($this->input);

        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                $pathinfo = pathinfo($this->input . DIRECTORY_SEPARATOR . $file);

                if ($this->media === $pathinfo['extension']) {
                    $this->analyseFile($this->input . DIRECTORY_SEPARATOR . $file);
                }
            }

            closedir($handle);
        } else {
            throw new Exception('Could not open "input" directory');
        }
    }

    private function analyseFile($file)
    {
        if ('exif' === $this->method) {
            $this->analyseByExif($file);
        }

        if ('lastModified' === $this->method) {
            $this->analyseByLastModified($file);
        }
        
        if ('name' === $this->method) {
            echo "hoho";
        }
    }

    private function analyseByExif($file)
    {
        $exif = @exif_read_data($file, null, false, false);

        if (false !== $exif) {
            $datetime = null;

            if (!empty($exif['DateTimeOriginal'])) {
                $datetime = strtotime($exif['DateTimeOriginal']);
            } elseif (!empty($exif['FileDateTime'])) {
                $datetime = strtotime($exif['FileDateTime']);
            }

            /* Wrong exif ? */
            if ('1970' === date('Y', $datetime)) {
                $datetime = null;
            }

            /* OK */
            if (!empty($datetime)) {
                $this->newFile($file, $datetime);
            }
        }
    }

    private function analyseByLastModified($file)
    {
        $this->newFile($file, filemtime($file));
    }

    private function newFile($file, $datetime)
    {
        $newFile = $this->output . DIRECTORY_SEPARATOR .
                date('Y', $datetime) . DIRECTORY_SEPARATOR .
                date('m', $datetime) . DIRECTORY_SEPARATOR .
                date('d', $datetime) . DIRECTORY_SEPARATOR .
                $this->prefix[$this->media] . date('Ymd_His', $datetime);

        /* Check if file already exists */
        if (is_file($newFile . '.' . $this->media)) {
            $newFile .= '_' . date('Ymd_His');
        } else {
            /* If the file does not exist, so need to create directories */
            if (!is_dir(dirname($newFile . '.' . $this->media))) {
                if (!mkdir(dirname($newFile . '.' . $this->media), 0777, true)) {
                    echo dirname($newFile . '.' . $this->media);
                    throw new Exception('Cannot create sub directories');
                }
            }
        }

        /* Move */
        if (rename($file, $newFile . '.' . $this->media)) {
            if (!touch($newFile . '.' . $this->media, $datetime)) {
                throw new Exception('Cannot change "last date modified" file');
            }
        } else {
            throw new Exception('Cannot move file');
        }
    }

}
