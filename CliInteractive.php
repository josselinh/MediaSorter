<?php

/**
 * Description of Cli
 *
 * @author josselin
 */
class CliInteractive
{
    
    public function ask($question = '', $possibilities = array(), $default = null)
    {
        $fullQuestion = $question;
        
        if (!empty($possibilities)) {
            $fullQuestion .= ' ('.implode(', ', $possibilities).')';
        }
        
        if (!is_null($default)) {
            $fullQuestion .= ' ['.$default.']';
        }
        
        $this->display($fullQuestion. ' ', 0);
        $response = trim(fgets(STDIN));
                
        if (0 === strlen($response) && !is_null($default)) {
            $response = $default;
        }
        
        if (!empty($possibilities)) {
            while (!in_array($response, $possibilities)) {
                $response = $this->ask($question, $possibilities, $default);
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
