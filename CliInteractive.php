<?php

/**
 * Description of Cli
 *
 * @author josselin
 */
class CliInteractive
{

    public function ask($question = '', $possibilies = array(), $default = null)
    {
        $question .= ' ';
        $response = null;
        
        if (!empty($possibilies) && !is_null($default)) {
            $question .= sprintf('[%s] ', $default);
        }
        
        while (!in_array($response, $possibilies)) {
            $this->display($question, 0);
            $response = trim(fgets(STDIN));
            
            if (empty($response)) {
                $response = $default;
            }
        }

        return $response;
    }

    public function display($string = '', $lineFeed = 1)
    {
        fwrite(STDOUT, $string);
        fwrite(STDOUT, str_repeat(PHP_EOL, $lineFeed));
    }

    public function error($string = '', $lineFeed = 1)
    {
        fwrite(STDERR, $string);
        fwrite(STDERR, str_repeat(PHP_EOL, $lineFeed));
    }
}
