<?php


namespace manguto\cms\lib;


class Javascript
{
    static function TimeoutDocumentLocation($location){
        $conteudo_js = "
          setTimeout(function(){
            document.location='$location';
           },100);  
        ";
        $return = self::Tags($conteudo_js);
        return $return;
    }
    
    static function Tags($conteudo_js){
        $return = '<script type="text/javascript">';
        $return .= $conteudo_js;
        $return .= '</script>';
        return $return;
    }
}

