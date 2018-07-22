<?php
namespace manguto\cms\lib;

class ProcessResult
{

    // ##################################### CHECK ERROR/WARNING/SUCCESS MSG EXISTS ###################################################
    // ##################################### CHECK ERROR/WARNING/SUCCESS MSG EXISTS ###################################################
    // ##################################### CHECK ERROR/WARNING/SUCCESS MSG EXISTS ###################################################
    /**
     * verifica se existem mensagens do tipo informado
     *
     * @param string $type
     * @throws \Exception
     * @return bool
     */
    public static function CHECK(string $type): bool
    {
        $type = ucfirst(strtolower($type));
        if ($type == 'Error' || $type == 'Success' || $type == 'Warning') {
            if (isset($_SESSION['ProcessResult'][$type]) && sizeof($_SESSION['ProcessResult'][$type]) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new \Exception("Tipo de resultado de processo incorreto ($type).");
        }
    }

    // ##################################### GET ERROR/WARNING/SUCCESS MSG ###################################################
    // ##################################### GET ERROR/WARNING/SUCCESS MSG ###################################################
    // ##################################### GET ERROR/WARNING/SUCCESS MSG ###################################################
    /**
     * obtem as mensagens (HTML) do tipo informado caso existam
     *
     * @param string $type
     * @return string
     */
    public static function GET(string $type): string
    {
        $type = ucfirst(strtolower($type));
        
        $return = [];
        if (self::check($type)) {
            foreach ($_SESSION['ProcessResult'][$type] as $identifier => $msg) {
                $return[] = self::{'get' . $type}($identifier);
            }
        }
        // $return[] = '<li>Process Result!</li>';
        if (sizeof($return) == 1) {
            $return = implode('', $return);
        } elseif (sizeof($return) > 1) {
            $return = '<ul><li>' . implode('</li><li>', $return) . '</li></ul>';
        } else {
            $return = '';
        }
        
        return $return;
    }

    // ##################################### ERROR CONTROL ###################################################
    // ##################################### ERROR CONTROL ###################################################
    // ##################################### ERROR CONTROL ###################################################
    public static function setError(string $msg)
    {
        $_SESSION['ProcessResult']['Error'][] = $msg;
    }

    private static function getError(string $identifier): string
    {
        $msg = '';
        if (isset($_SESSION['ProcessResult']['Error'][$identifier])) {
            $msg = $_SESSION['ProcessResult']['Error'][$identifier];
            unset($_SESSION['ProcessResult']['Error'][$identifier]);
        }
        return $msg;
    }

    // ################################### WARNING CONTROL ###################################################
    // ################################### WARNING CONTROL ###################################################
    // ################################### WARNING CONTROL ###################################################
    public static function setWarning(string $msg)
    {
        $_SESSION['ProcessResult']['Warning'][] = $msg;
    }

    private static function getWarning(string $identifier): string
    {
        $msg = '';
        if (isset($_SESSION['ProcessResult']['Warning'][$identifier])) {
            $msg = $_SESSION['ProcessResult']['Warning'][$identifier];
            unset($_SESSION['ProcessResult']['Warning'][$identifier]);
        }
        return $msg;
    }

    // ################################### SUCCESS CONTROL ###################################################
    // ################################### SUCCESS CONTROL ###################################################
    // ################################### SUCCESS CONTROL ###################################################
    public static function setSuccess(string $msg)
    {
        $_SESSION['ProcessResult']['Success'][] = $msg;
    }

    private static function getSuccess(string $identifier): string
    {
        $msg = '';
        if (isset($_SESSION['ProcessResult']['Success'][$identifier])) {
            $msg = $_SESSION['ProcessResult']['Success'][$identifier];
            unset($_SESSION['ProcessResult']['Success'][$identifier]);
        }
        return $msg;
    }

    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ VARIABLES PASSAGE CONTROL @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ VARIABLES PASSAGE CONTROL @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ VARIABLES PASSAGE CONTROL @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ VARIABLES PASSAGE CONTROL @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ VARIABLES PASSAGE CONTROL @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    
    public static function setVar(string $variableName,$variableValue)
    {   
        if(isset($_SESSION['ProcessResult']['Parameters'][$variableName])){
            throw new \Exception("Parâmetro já definido na sessão ('$variableName').");
        }
        $_SESSION['ProcessResult']['Parameters'][$variableName] = serialize($variableValue);        
    }

    public static function checkVar($variableName)
    {
        if (isset($_SESSION['ProcessResult']['Parameters'][$variableName])) {
            return true;
        } else {
            return false;
        }
    }

    public static function getVar(string $variableName,bool $unset=true)
    {
        if (self::CHECK_VAR($variableName)) {
            $return = $_SESSION['ProcessResult']['Parameters'][$variableName];
            unset($_SESSION['ProcessResult']['Parameters'][$variableName]);
        } else {
            throw new \Exception("Variável não encontrada na sessão ($variableName).");
        }
        return $return;
    }


}

?>