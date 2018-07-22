<?php

namespace manguto\cms\help;

class Help{
    
    
    /**
     * retorna uma string HTML com a representacao do conteudo do array
     * @param number $level
     * @return string
     */
    static function debug_var($variable, $level = 0)
    {
        $type = gettype($variable);
        // boolean, integer, double, string, NULL, array, object, resource, unknown type
        
        { // td key attr
            $td_attr = " title='$type ' style='cursor:pointer; text-align:right;'";
        }
        
        $return = array();
        $return[] = "<table border='0' style='border-left:solid 1px #aaa; border-bottom:solid 1px #aaa; width:100%;'>";
        { // ------------------------------------------------------------------------------------------------
            if ($type == 'boolean' || $type == 'integer' || $type == 'double' || $type == 'string' || $type == 'NULL') {
                
                // ajuste para melhor exibição
                $variable = trim($variable) == '' ? '&nbsp;' : '= ' . $variable;
                
                $return[] = "<tr>";
                $return[] = "<td $td_attr>$variable</td>";
                $return[] = "</tr>";
            } else if ($type == 'array' || $type == 'object') {
                
                // conversao do objeto em array
                if ($type == 'object') {
                    $variable = (array) $variable;
                    $tagPre = '-> ';
                    $tagPos = '';
                } else {
                    $tagPre = '[';
                    $tagPos = ']';
                }
                foreach ($variable as $key => $var) {
                    $return[] = "<tr>";
                    $return[] = "<td $td_attr>$tagPre$key$tagPos</td>";
                    $return[] = "<td>" . debug_var($var, ($level + 1)) . "</td>";
                    $return[] = "</tr>";
                }
            } else if ($type == 'resource') {
                $return[] = "<tr>";
                $return[] = "<td $td_attr>'resource'</td>";
                $return[] = "</tr>";
            } else {
                $return[] = "<tr>";
                $return[] = "<td $td_attr>'unknown type'</td>";
                $return[] = "</tr>";
            }
        } // ------------------------------------------------------------------------------------------------
        $return[] = "</table>";
        $return = implode(chr(10), $return);
        return $return;
    }
    
    /**
     * debug
     * @param bool $die
     * @param bool $backtrace
     */
    static function deb($var,bool $die = true,bool $backtrace = true)
    {
        
        // backtrace show?
        if ($backtrace) {
            $backtrace = self::get_backtrace();
            $backtrace = str_replace("'", '"', $backtrace);
        } else {
            $backtrace = '';
        }
        
        // var_dump to string
        ob_start();
        var_dump($var);
        $var = ob_get_clean();
        
        echo "<pre style='cursor:pointer;' title='$backtrace'>$var</pre>";
        
        if ($die)
            die();
    }
    
    /**
     * debug code
     * @param bool $die
     * @param bool $backtrace
     */
    static function debCode($var,bool $die = true,bool $backtrace = true)
    {
        
        // backtrace show?
        if ($backtrace) {
            $backtrace = self::get_backtrace();
        } else {
            $backtrace = '';
        }
        
        // var_dump to string
        ob_start();
        var_dump($var);
        $var = ob_get_clean();
        echo "<textarea style='border:solid 1px #000; padding:5px; width:90%; height:400px;' title='$backtrace'>$var</textarea>";
        if ($die)
            die();
    }
    
    /**
     * 
     */
    static function get_backtrace()
    {
        $trace = debug_backtrace();
        
        // removao da primeira linha relativa a chamada a esta mesma funcao
        array_shift($trace);
        
        // inversao da ordem de exibicao
        krsort($trace);
        
        $log = '';
        $step = 1;
        foreach ($trace as $i => $t) {
            
            if (isset($t['file'])) {
                $file = $t['file'];
                $line = $t['line'];
                $func = $t['function'];
                $log .= "#" . $step ++ . " => $func() ; $file ($line)\n";
            }
        }
        {
            // identacao
            // $log = CSVHelp::IdentarConteudoCSV($log,25,'direita');
            $log = str_replace(';', '', $log);
            // $log=str_replace(' ', '_', $log);
        }
        
        return $log;
    }
    
    /**
     * Ajusta o caminho informado com o DIRECTORY SEPARATOR correto do sitema *
     * @param string $path
     * @return string
     */
    static function fixds(string $path): string
    {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        return $path;
    }
    
}


?>