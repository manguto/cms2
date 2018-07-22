<?php

namespace manguto\cms\lib;



class Arquivos {
	static function obterExtensao($filename) {
		$extensao = pathinfo ( $filename, PATHINFO_EXTENSION );
		// debug($extensao);
		return $extensao;
	}
	static function obterTamanho($filename) {
		// debug($filename,0);
		$return = filesize ( $filename );
		// debug($return,0);
		return $return;
	}

	static function obterCaminho($filepath) {
		$return = str_replace ( self::obterNomeArquivo ( $filepath ), '', $filepath );
		return $return;
	}
	static function obterNomeArquivo($filepath,$withExtension=true) {
		// debug($filepath);
		$teste = strpos ( $filepath, chr ( 47 ) ); // '/'
		// debug($teste);
		if ($teste !== false) {
			$separador = chr ( 47 ); // '/'
		} else {
			$separador = chr ( 92 ); // '\';
		}
		// debug($separador);

		$filepath = explode ( $separador, $filepath );
		$filepath = array_pop ( $filepath );

		{//remove extension
		    if($withExtension===false){
		        $ext = self::obterExtensao($filepath);
		        $filepath = str_replace('.'.$ext, '', $filepath);
		    }
		}		
		
		return $filepath;
	}
	static function obterNomePasta($filepath) {
		$filepath = Diretorios::fixDirectorySeparator ( $filepath );
		$filepath = explode ( DIRECTORY_SEPARATOR, $filepath );
		// debug($filepath);
		{ // ---verificacao se parametro eh de uma pasta ou de um arquivo
			if (strpos ( $filepath [sizeof ( $filepath ) - 1], '.' ) !== false) {
				$arquivo = true;
			} else {
				$arquivo = false;
			}
		}
		if ($arquivo) {
			array_pop ( $filepath );
		}

		$filepath = array_pop ( $filepath );
		return $filepath;
	}

	/**
	 * OBTEM O CONTEUDO (STRING) DE UM ARQUIVO
	 * E CASO NÃO O ENCONTRE, SOLTA UMA EXCESSÃO.
	 *
	 * @throws \Exception
	 */
	static function obterConteudo($filename, $throwException = true) {
		//############ log::open(__METHOD__,"Obtém o conteúdo do arquivo '$filename'.");
		if (file_exists ( $filename )) {
			//############ log::add("Arquivo encontrado.");
			$return = file_get_contents ( $filename );
			if($return!==false){
				//############ log::add("Conteúdo do arquivo obtido com sucesso.");
				return $return;
			}else{
				if ($throwException === true) {
					throw new \Exception ( "Não foi possível realizar a leitura do arquivo solicitado ('$filename')." );
				} else {
					//############ log::add("Não foi possível realizar a leitura do arquivo solicitado ('$filename').");
					return false;
				}
			}
		} else {
			if ($throwException === true) {
				throw new \Exception ( "Arquivo (e consequentemente seu conteúdo) não encontrado. ('$filename')." );
			} else {
				//############ log::add("Arquivo (e consequentemente seu conteúdo) não encontrado. ('$filename').");
				return false;
			}
		}
		//############ log::close();
	}

	static function escreverConteudo($filename, $data) {
		// ---verificar diretorio
		$caminho = self::obterCaminho ( $filename );
		Diretorios::mkdir ( $caminho );
		// ---salvar o arquivo
		if (! file_put_contents ( $filename, $data )) {
			throw new \Exception ( "Não foi possível salvar o arquivo solicitado ($filename)." );
		}
		return true;
	}

	static function excluir($filename) {
		// ---verificar diretorio
		if(file_exists($filename)){
			if(!unlink($filename)){
				throw new \Exception ( "Não foi possível excluir o arquivo solicitado ($filename)." );
			}
		}
		return true;
	}

}

?>