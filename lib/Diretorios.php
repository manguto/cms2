<?php

namespace manguto\cms\lib;


class Diretorios {
	
	/**
	 * obtem arquivos e/ou pastas do diretorio informado,e de acordo com as condicoes dos parametros solicitados.
	 * @param string $path
	 * @param bool $recursive
	 * @param bool $filesAllowed
	 * @param bool $foldersAllowed
	 * @param array $allowedExtensionArray
	 * @return string[]|string[]|mixed[]|string[][]|string[][]|mixed[][]
	 */	 
	static function obterArquivosPastas(string $path,bool $recursive,bool $filesAllowed,bool $foldersAllowed,array $allowedExtensionArray = array()) {
		//die($path);
		$path = self::fixDirectorySeparator ( $path );
		
		if ($filesAllowed == false) {
			$allowedExtensionArray = false;
		}
		
		$dh = opendir ( $path );
		
		$return = array ();
		
		while ( false !== ($filename = readdir ( $dh )) ) {
			
			if ($filename == '.' || $filename == '..') {
				continue;
			}
			
			$filename = $path . DIRECTORY_SEPARATOR . $filename ;
			
			$filename = self::fixDirectorySeparator ( $filename );
			
			if (is_dir ( $filename )) {
				// --- ADICIONA FOLDERNAME
				$return [] = $filename . DIRECTORY_SEPARATOR;
				
				if ($recursive) {
					$filename = Diretorios::obterArquivosPastas ( $filename, $recursive, $filesAllowed, $foldersAllowed, $allowedExtensionArray);
					if (sizeof ( $filename ) > 0) {
						foreach ( $filename as $f ) {
							// --- ADICIONA SUB-FILENAMES
							$return [] = $f;
						}
					}
				}
			} else {
				// --- ADICIONA FILENAME
				$return [] = $filename;
			}
		}
		
		foreach ( $return as $k => $filename ) {
			if (is_dir ( $filename ) && ! $foldersAllowed) {
				unset ( $return [$k] );
			}
			if (is_file ( $filename ) && $allowedExtensionArray === false) {
				unset ( $return [$k] );
			}
			if (is_file ( $filename ) && $allowedExtensionArray !== false) {
				$extension = Arquivos::obterExtensao ( $filename );
				// debug($extension,0);
				// debug($allowedExtensionArray,0);
				if (sizeof ( $allowedExtensionArray ) > 0 && ! in_array ( $extension, $allowedExtensionArray )) {
					unset ( $return [$k] );
				}
			}
		}
		
		return $return;
	}
	static function fixDirectorySeparator($path) {
		$path = str_replace ( '/', DIRECTORY_SEPARATOR, $path );
		$path = str_replace ( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path );
		return $path;
	}
	
	/**
	 * CRIA UM DIRETORIO
	 *	 
	 */
	static function mkdir($pathname, $recursivo = true) {
		if (! file_exists ( $pathname )) {
			$return = mkdir ( $pathname, 0777, $recursivo );
			if (! $return) {
				throw new \Exception ( "Não foi possível criar o diretório '$pathname'." );
			}
		}
		return true;
	}
	
	/**
	 * REMOVE PASTA E SEU CONTEUDO
	 *
	 */
	static function rmdir($path, $exceptionArray = array()) {
		//debug($X);
		$path = self::fixDirectorySeparator ( $path );
	
		if (substr ( $path, - 1, 1 ) != DIRECTORY_SEPARATOR) {
			$path .= DIRECTORY_SEPARATOR;
		}
		// $deleteFolder_array = glob ( $path . '/*' );
		$deleteFolder_array = array_diff ( scandir ( $path ), array (
				'.',
				'..' 
		) );
		
		{ // --- verifica excecoes e as retira do array para delecao
		  
			// --- arquivos de admin (.*.*)
			foreach ( $deleteFolder_array as $object ) {
				if (substr ( $object, 0, 1 ) == '.') {
					$key = array_search ( $object, $deleteFolder_array );
					unset ( $deleteFolder_array [$key] );
				}
			}
			// --- excecoes informadas
			foreach ( $exceptionArray as $exception ) {
				$exception = str_replace ( $path, '', $exception );
				if (in_array ( $exception, $deleteFolder_array )) {
					unset ( $deleteFolder_array [array_search ( $exception, $deleteFolder_array )] );
				}
			}
		}
		
		//#######################################
		//debug ( $deleteFolder_array);
		//#######################################
		
		error_reporting ( E_ALL ^ E_WARNING );
		foreach ( $deleteFolder_array as $object ) {
			$object = $path . $object;
			
			if (is_dir ( $object )) {
				
				// ------------------------- (>>>) --------------------------------------------
				self::rmdir ( $object ,$exceptionArray);
			} else {
				
				// ------------------------- remover arquivo --------------------------------
				if (! unlink ( $object )) {
					throw new \Exception ( "NAO FOI POSSIVEL REMOVER O ARQUIVO '$object'." );
				} else {
					//echo "Arquivo removido com sucesso o arquivo '$object'.<br/>";
				}
			}
		}
		if (! in_array ( $path, $exceptionArray )) {
			// ------------------------- remover diretorio ----------------------------			
			if (count ( $path ) == 1) {
				// debug ( self::obterArquivosPastas ( $path, true, true, true ), 0 );
				
				//---truque para efetivamente deletar um diretorio
				$handle = opendir($path);
				closedir($handle);	
				
				if (! rmdir ( $path )) {
					$msg = "<b style='color:#000; background:#f00; text-decoration:underline; padding:2px;'>NAO FOI POSSIVEL REMOVER A PASTA '$path'.</b>";
					throw new \Exception ( $msg );
					echo $msg;
				} else {
					//echo "Diretório removido com sucesso o arquivo '$path'.<br/>";
				}
			}
		}		
		return true;
	}
	
	/**
	 * VERIFICA SE O DIRETORIO ESTA VAZIO
	 *
	 */
	static function is_dir_empty($dir) {
		if (! is_readable ( $dir )) {
			$return = NULL;
		} else {
			$return = (count ( scandir ( $dir ) ) == 2);
		}
		return $return;
	}
	
	
	static function REMOVER_ADMIN___CUIDADO($deleteAnyway=false){
	    
	    //diretorio a ser removido
	    $path = '.'.DIRECTORY_SEPARATOR;
	    
	    //diretorio (e arquivos) a serem mantidos
	    $except = array();
	    $except[] = $path;
	    $except[] = $path.PROJETO_FOLDERNAME;
	    $except[] = $path.'sis';
	    $except[] = $path.'index.php';
	    
	    if(isset($_GET['reset']) || $deleteAnyway){
	        session_destroy();
	        session_start();
	        return self::rmdir($path, $except);
	    }
	}
	
}