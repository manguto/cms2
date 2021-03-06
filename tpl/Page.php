<?php

namespace manguto\cms\gms;

use Rain\Tpl;
use manguto\cms\lib\Arquivos;

/**
 * Documentation for web designers:
 * https://github.com/feulf/raintpl/wiki/Documentation-for-web-designers * 
 * @author Marcos Torres *
 */
class Page{

	private $tpl;
	private $tpl_dir;
	private $options = [];
	private $optionsDefault = [
		"data"=>[]
	];
	
	public function __construct($opts=array(),$tpl_dir='/views/site/')
	{	    
		$this->options = array_merge($this->optionsDefault,$opts);		
		//deb($this->options);
		
		//tpl_dir
		$this->tpl_dir = $tpl_dir;
		
		// config
		$config = array(
			"tpl_dir"       => ROOT_TPL.$tpl_dir,			
		    "cache_dir"     => ROOT_TPL."/views/cache/",
			"debug"         => true  // set to false to improve the speed
		);

		Tpl::configure( $config );
		
		// create the Tpl object
		$this->tpl = new Tpl;
		
		$this->assignDataArray($this->options['data']);
		
				
	}
	
	private function assignDataArray($data=array())
	{
		foreach ($data as $key=>$value){		    
			$this->tpl->assign($key,$value);
		}
	}
	
	/**
	 * Ajusta/corrige template (HTML) decorrentes do espacamento 
	 * colocado antes das chaves ("}") realizado automaticamente 
	 * pelo Eclipse quando do CTRL+F
	 * @param string $filename
	 */
	private function fixTpl(string $filename){
	    //ajuste nome comleto arquivo
	    $filename = ROOT_TPL.$this->tpl_dir.$filename.'.html';
	    //obtem conteudo
	    $data = Arquivos::obterConteudo($filename);
	    //debCode($data);
	    //realiza correcoes se necessario
	    if(strpos($data, ' }')!==false){
	        $data = str_replace(' }', '}', $data);
	        //reescreve conteudo
	        Arquivos::escreverConteudo($filename, $data);
	    }else{
	        //nenhum ajuste necessario
	    }
	}
	
	public function setTpl($filename,$data=array(),$toString=false)

	{
		$this->assignDataArray($data);
		
		$this->fixTpl($filename);
		
		$html = $this->tpl->draw($filename,true);
		
		$html = self::TplReferencesFix($html);
		
		if($toString){
		    return $html;
		}else{
		    echo $html;
		}		
	}
	
	static function TplReferencesFix($html){
	    
	    if(!defined('ROOT')){
	        throw new \Exception("Constante 'ROOT' não encontrada (definida).");
	    }
	    
	    {//exceptions
	        $exc = [];
	        $exc[] = 'href="http';
	        $exc[] = "href='http";
	        
	        $exc[] = 'src="http';
	        $exc[] = "src='http";
	        
	        $exc[] = 'action="http';
	        $exc[] = "action='http";
	        //--------------------------
	        foreach ($exc as $k=>$v){
	            $html = str_replace($v,'#_'.$k.'_#',$html);
	        }
	    }
	    
	    //--- href
	    $html = str_replace('href="','href="'.ROOT,$html);
	    $html = str_replace("href='","href='".ROOT,$html);
	    //--- href errors fix
	    $html = str_replace(ROOT.'javascript','javascript',$html); //<a href='javascript:void(0)'>
	    $html = str_replace(ROOT.'#','#',$html); //<a href='#'>
	    
	    //--- src
	    $html = str_replace('src="','src="'.ROOT,$html);
	    $html = str_replace("src='","src='".ROOT,$html);
	    
	    //--- action
	    $html = str_replace('action="','action="'.ROOT_ACTION,$html);
	    $html = str_replace("action='","action='".ROOT_ACTION,$html);
	    
	    {//exception fix
	        foreach ($exc as $k=>$v){
	            $html = str_replace('#_'.$k.'_#',$v,$html);
	        }
	    }
	    
	    return $html;
	}
	
	public function __destruct()
	{
	   //...
	}	

}



?>