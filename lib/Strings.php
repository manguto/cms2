<?php

namespace manguto\cms\lib;


class Strings {
	static function RemoverCaracteresDeControle($texto, $permitirQuebradeLinha = true) {
		if ($permitirQuebradeLinha) {
			$texto = preg_replace ( '/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $texto );
		} else {
			$texto = preg_replace ( '/[\x00-\x1F\x7F]/', '', $texto );
		}
		return $texto;
	}
	static function RemoverCaracteresEspeciais($string){	    
	    // Replaces all spaces with hyphens.
	    //$string = str_replace(' ', '-', $string); 	    
	    // Removes special chars.
	    $string = preg_replace('/[^A-Za-z0-9\- ]/', '', $string); 	    
	    return $string;
	}
	
	static function abreviacao($string){
	    $string = trim($string);
	    $string_array = explode(' ', $string);
	    $return = '';
	    foreach ($string_array as $s){
	        $return.=substr($s,0,1);
	    }
	    return $return;
	}
	
	
	static function RemoverEspacamentosRepetidos($texto) {
		$texto = preg_replace('/[ ]([ ])+/', ' ',$texto);
		return $texto;
	}
	static function ObterASCIICodes($string){
	    $return = array();
	    for($c=0;$c<strlen($string);$c++){
	        
	        $caracter = $string[$c];
	        $ascii = ord($caracter);
	        
	        $return[] = "<a href='#' title='$caracter($ascii)'>$caracter</a>";
	    }
	    return implode('', $return);
	}
	
	static function RemoverCaracteresInvisiveis($string){
	    $string_ = explode(' ', $string);
	    foreach ($string_ as $k=>$s){
	        $string_[$k]=trim($s);
	    }
	    return implode(' ', $string_);
	}
	
	/**
	 * retorna um caractere aleatorio dentre a faixa definida com relacao aa tabela ascii
	 * @param number $ascii_in
	 * @param number $ascii_out
	 * @return string
	 */
	static function AleatorioCaractere($ascii_in=97,$ascii_out=122):string{
	    return chr(rand($ascii_in,$ascii_out));	    
	}
	
	/**
	 * retorna uma string aleatoria com a quantidade de caracteres definida, dentre a faixa informada (ascii)
	 * @param number $quantCaracteres
	 * @param number $ascii_in
	 * @param number $ascii_out
	 * @return string
	 */
	static function AleatoriaString($quantCaracteres=1,$ascii_in=97,$ascii_out=122):string{
	    $quantCaracteres = intval($quantCaracteres);
	    $return = '';
	    for($i=0;$i<$quantCaracteres;$i++){
	        $return.=self::AleatorioCaractere($ascii_in,$ascii_out);
	    }
	    return $return;	    	    
	}
	
	/**
	 * retorna uma string aleatoria de NUMEROS com a quantidade de caracteres definida
	 * @param number $quantCaracteres
	 * @param number $ascii_in
	 * @param number $ascii_out
	 * @return string
	 */
	static function AleatoriosNumeros($quantCaracteres=1):string{
	    $quantCaracteres = intval($quantCaracteres);
	    //0-9
	    $ascii_in=48;
	    $ascii_out=57;
	    $return = '';
	    for($i=0;$i<$quantCaracteres;$i++){
	        $return.=self::AleatorioCaractere($ascii_in,$ascii_out);
	    }
	    return $return;
	}
	
	/**
	 * gera uma sequencia similar a de um cpf sem validacao
	 * @return string
	 */
	static function AleatorioCPF():string{
	    return self::AleatoriosNumeros(3).'.'.self::AleatoriosNumeros(3).'.'.self::AleatoriosNumeros(3).'-'.self::AleatoriosNumeros(2);
	}
	
	
	
	
}


?>
