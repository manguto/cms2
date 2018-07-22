<?php

namespace manguto\cms\lib;


use stdClass;

class CSV {
	const separadorRegistros = '
';
	const separadorRegistrosTEMP = '(_)';
	
	const separadorParametros = ';';
	const separadorParametrosTEMP = '(;)';
	
	
	public static function CSVToArray($csvContent) {
		$csvContentLines = explode ( CSV::separadorRegistros, $csvContent );
	
		// nomes das colunas (1a linha)
		$colNames = array_shift ( $csvContentLines );
		$colNames = explode ( CSV::separadorParametros, $colNames );
	
		// valores das linhas
		$return = array ();
		foreach ( $csvContentLines as $lineNumber => $line ) {
			$line = trim ( $line );
			if ($line == '')
				continue;
				
			$atributos = explode ( CSV::separadorParametros, $line );
				
			foreach ( $atributos as $k => $v ) {
				
				$v = trim($v);
				
				// exception
				$v = str_replace ( CSV::separadorRegistrosTEMP, CSV::separadorRegistros, $v );
				$v = str_replace ( CSV::separadorParametrosTEMP, CSV::separadorParametros, $v );
								
				$colname = trim ( $colNames [$k] );
				$return [$lineNumber] [$colname] = $v;
			}
		}
		return $return;
	}
	
	public static function ArrayToCSV($arrayContent) {
		$colNames = array ();
		$csvContentLines = array ();
		foreach ( $arrayContent as $line ) {
			$csvLine = array ();
			foreach ( $line as $colname => $content ) {
	
				// exception
				$content = str_replace ( CSV::separadorRegistros, CSV::separadorRegistrosTEMP, $content );
				$content = str_replace ( CSV::separadorParametros, CSV::separadorParametrosTEMP, $content );
	
				if (! in_array ( $colname, $colNames )) {
					$colNames [] = $colname;
				}
				$csvLine [] = $content;
			}
			$csvLine = implode ( CSV::separadorParametros, $csvLine );
			$csvContentLines [] = $csvLine;
		}
		{ // add colnames to the beginning of array
			$colNamesCSV = implode ( CSV::separadorParametros, $colNames );
			array_unshift ( $csvContentLines, $colNamesCSV );
		}
	
		$return = implode ( CSV::separadorRegistros, $csvContentLines );
	
		return $return;
	}
	
	public static function CSVToObjectArray($CSV)
	{		
		$registroBaseCSVArray = CSV::CSVToArray($CSV);
		
		$return = array();
		if (! is_array($registroBaseCSVArray))
			throw new \Exception("Parâmetro deve ser um array.");
		foreach ($registroBaseCSVArray as $registroBaseCSV) {
			if (! is_array($registroBaseCSV))
				throw new \Exception("Parâmetro deve ser um array.");
			$registroTmp = new stdClass();
			foreach ($registroBaseCSV as $parametro => $valor) {
				$registroTmp->$parametro = $valor;
			}
			$return[] = $registroTmp;
		}
		return $return;
	}
}

?>
