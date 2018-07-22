<?php
namespace manguto\cms\lib;

class Arrays
{
    
    /**
     * Retorna um array com um unico nivel, atraves de outro multi-nivel cujas chaves serao as chaves
     * @param array $arquivoArray
     * @return array
     */
    static function arrayMultiNivelParaSimples(array $arquivoArray):array
    {
        $return = array();
        foreach ($arquivoArray as $a) {
            if (is_array($a)) {
                $return2 = self::arrayMultiNivelParaSimples($a);
                foreach ($return2 as $r) {
                    $return[] = $r;
                }
            } else {
                $return[] = $a;
            }
        }
        return $return;
    }

    
    static function arrayShow($array, string $arrayName = '', string $continuacao = '', int $level = 1)
    {
        $return = [];
        
        // array name
        if ($arrayName != '' && $level == 1) {
            $pre = "\$$arrayName [";
            $pos = "]";
        } else {
            $pre = "$continuacao [";
            $pos = "]";
        }
        
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                // deb($v,0);
                $termo = $pre . $k . $pos;
                $return[] = self::arrayShow($v, $k, $termo, ++ $level);
            }
        } else {
            // $termo = $pre.$arrayName.$pos;
            return "$continuacao = \"$array\"<br/>";
        }
        
        return implode(chr(10), $return);
    }
    
    static function strtolower($array)
    {
        foreach ($array as &$v) {
            if(is_array($v)){
                $v = self::strtolower($v);
            }else{
                $v = strtolower($v);
            }
            
        }
        return $array;
    }
    static function utf8_encode($array)
    {
        foreach ($array as &$v) {
            if(is_array($v)){
                $v = self::utf8_encode($v);
            }else{
                $v = utf8_encode($v);
            }
            
        }
        return $array;
    }

}