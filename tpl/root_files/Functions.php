<?php
use lib\model\User;
use manguto\cms\lib\ProcessResult;

// --------------------------------------------------------- DEFINITIONS
define('BR', '<br/>');
define('HR', '<hr/>');
define('DR', DIRECTORY_SEPARATOR);

// --------------------------------------------------------- FUNCTIONS

{

    // ---------------------------------- USER & SESSION
    function checkUserLogged()
    {
        return User::checkUserLogged();
    }

    function getUserName()
    {
        $user = User::getSessionUser();
        return $user->getName();
    }

    function checkUserLoggedAdmin()
    {
        if (checkUserLogged()) {
            $user = User::getSessionUser();
            return intval($user->getInadmin()) == 0 ? false : true;
        } else {
            return false;
        }
    }
}

// ---------------------------- erro / success / warning
function checkError()
{
    return ProcessResult::CHECK('error');
}

function checkSuccess()
{
    return ProcessResult::CHECK('success');
}

function checkWarning()
{
    return ProcessResult::CHECK('warning');
}

function getError()
{
    return ProcessResult::GET('error');
}

function getWarning()
{
    return ProcessResult::GET('warning');
}

function getSuccess()
{
    return ProcessResult::GET('success');
}

// --------------------------------------------------------- SISTEM HELP
function headerLocation($url)
{
    header('Location: ' . ROOT_LOCATION . $url);
}

function headerLocationPost(string $URLAbsolute,array $variables=[])
{
    $url = ROOT_LOCATION . $URLAbsolute;
    
    $inputs = '';
    foreach ($variables as $key => $value) {
        
        //ajuste no caso de parametros informados em array (checkboxes...)
        if(!is_array($value)){
            $inputs .= "$key: <input type='text' name='$key' value='$value' class='form-control mb-2' style='display:none;'>";
        }else{
            $key = $key.'[]';
            foreach ($value as $v){
                $inputs .= "$key: <input type='text' name='$key' value='$v' class='form-control mb-2' style='display:none;'>";
            }
            
        }
    }
    
    $html = "<!DOCTYPE html>
                <html>
                    <head>
                        <title>REDIRECTION...</title>	
                    </head>
                    <body>
                        <section>
                        	<div class='container'>     		
                        		<form method='post' action='$url' id='postRedirect' style='display:none;'>
                                    $inputs		
                        			<input type='submit' value='CLIQUE AQUI PARA CONTINUAR...' style='display:none;'>
                        		</form>                        		
                        	</div>
                        </section>
                    </body>
                </html>
                <script type='text/javascript'>
                    (function() {
                        document.getElementById('postRedirect').submit();                   
                    })();                    
                </script>

";
    echo $html;
}

function getCharset($var)
{
    return mb_detect_encoding($var);
}

function detectUTF8($string)
{
    return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )+%xs', $string);
}

function issetPOST($varname, $default = '')
{
    if (isset($_POST[$varname])) {
        return trim($_POST[$varname]);
    } else {
        return $default;
    }
}

function issetGET($varname, $default = '')
{
    if (isset($_GET[$varname])) {
        return trim($_GET[$varname]);
    } else {
        return $default;
    }
}

// --------------------------------------------------------- SISTEM HELP - MAGT

/**
 * retorna uma string HTML com a representacao do conteudo do array
 */
function debug_var($variable, $level = 0)
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

function deb($var, $die = true, $backtrace = true)
{
    
    // backtrace show?
    if ($backtrace) {
        $backtrace = get_backtrace();
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

function debCode($var, $die = true, $backtrace = true)
{
    
    // backtrace show?
    if ($backtrace) {
        $backtrace = get_backtrace();
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

function get_backtrace()
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
 */
function fixds($path): string
{
    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
    return $path;
}

?>