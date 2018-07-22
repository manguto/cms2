<?php
namespace manguto\cms\ldb;

use manguto\cms\help\Help;

class Model
{

    protected $values = [];
  
    public function __construct($identifierValue = null)
    {
        
        
        if ($identifierValue != null) {
            $this->setIdentifier((int) $identifierValue);
            $this->get();
        } else {
            $this->setIdentifier((int) 0);
            $this_shaped = Database::table_entry_shape($this);            
            $this->setData($this_shaped->getData());            
        }
    }


    // magic methods GET & SET
    public function __call($name, $args)
    {
        $method = substr($name, 0, 3);
        $fieldName = strtolower(substr($name, 3, strlen($name)));
        
        switch ($method) {
            case "get":
                $return = (isset($this->values[$fieldName]) ? ($this->values[$fieldName]) : NULL);
                break;
            
            case "set":
                $return = true;
                $this->values[$fieldName] = ($args[0]);
                break;
            default:
                throw new \Exception("Parâmetro de acesso incorreto (model->$name).");
        }
        return $return;
    }

    /**
     * define os parametros ou atributos do modelo
     * atraves de um array passado
     * @param array $data
     */
    public function setData(array $data = array())
    {
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $this->{"set" . $key}($value);
        }
    }

    /**
     * obtem o conteudo do modelo em forma de array
     * @return array
     */
    public function getData():array
    {
        return $this->values;
    }

    /**
     * remove determinado parametro (coluna ou atributo) do modelo
     * @param string $dataName
     */
    public function dataRemove(string $dataName){
        if(isset($this->values[$dataName])){
            unset($this->values[$dataName]);
        }
    }
    
    /**
     * obtem modelo padrao. caso o identificador seja informado carrega-o da base de dados
     * @throws \Exception
     * @return array
     */
    public function get():array
    {
        $db = new Database();
        $results = $db->getTableEntries($this->getModelName(), [
            $this->getIdentifierName() => $this->getIdentifier()
        ]);
        if (sizeof($results) == 1) {
            $this->setData($results[0]->getData());
        } elseif (sizeof($results) > 1) {
            throw new \Exception("Foram encontrados mais de um(a) '" . $this->getModelName() . "' para o identificador '" . $this->getIdentifier() . "'.");
        } else {
            throw new \Exception("Não foram encontrados(as) '" . $this->getModelName() . "' para o identificador '" . $this->getIdentifier() . "'.");
        }
        return $results;
    }

    /**
     * salva modelo na base de dados
     */
    public function save()
    {
        $db = new Database();
        
        $db->table_entry_save($this);
    }

    /**
     * remove modelo da base de dados
     */
    public function delete()
    {
        $db = new Database();
        
        $db->table_entry_delete($this);
    }

      
    /**
     * obtem o nome da classe do modelo atual
     * @return string
     */
    public function getModelName():string
    {
        $modelName = get_class($this);
        //deb($modelName,0);
        //deb(DIRECTORY_SEPARATOR,0);
        $modelName = Help::fixds($modelName);
        $modelName = explode(DIRECTORY_SEPARATOR, $modelName);
        //deb($modelName);
        $modelName = array_pop($modelName);
        return $modelName;
    }

    /**
     * obtem o nome do identificador do modelo atual
     * @return string
     */
    public function getIdentifierName():string
    {
        $identifierName = strtolower($this->getModelName() . 'id');
        return $identifierName;
    }

    /**
     * obtem o valor do identificador (identificador propriamente dito)
     * @return int
     */
    public function getIdentifier():int
    {
        $method = 'get' . $this->getIdentifierName() . '';
        return $this->$method();
    }

    /**
     * define o valor do identificador do modelo atual
     * @param int $identifierValue
     * @return int
     */
    public function setIdentifier(int $identifierValue):int
    {
        $method = 'set' . $this->getIdentifierName() . '';
        return $this->$method($identifierValue);
    }
    
    public function loadExternalReferences(){                
        foreach ($this->values as $key=>$value){
            //deb("$key $value");
            if(substr($key, -2,2)=='id'){
                $possibleTableName = ucfirst(str_replace('id', '', $key));
                //deb("$possibleTableName | ".$this->getModelName());
                
                //evita o re-carregamento do proprio objeto
                if($possibleTableName==$this->getModelName()){
                    continue;
                }
                //deb("$key $value");
                if(true){ //class_exists($possibleTableName,true)
                    //deb($possibleTableName);
                    $modelPossibleTableName = '\lib\model\\'.$possibleTableName;
                    //deb($modelPossibleTableName);
                    $referencedObjectTemp = new $modelPossibleTableName($value);
                    //deb($referencedObjectTemp);
                    $referencedObjectTemp = $referencedObjectTemp->getData();
                    foreach ($referencedObjectTemp as $key2=>$value2){
                        $method = "set".strtolower($possibleTableName).'_'.$key2;
                        $this->$method($value2);
                    }
                    //deb($this);
                }
            }
        }
    }
    
    
    /**
     * retorna o modelo em forma de string
     * @return string
     */
    public function __toString():string
    {
        $return = array();
        $values = $this->values;
        foreach ($values as $c => $v) {
            $return[] = "$c=$v";
        }
        $return = self::getModelName($this) . " [ " . implode(', ', $return) . " ]";
        
        return $return;
    }
    
    
}

?>