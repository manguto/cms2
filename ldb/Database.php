<?php
namespace manguto\cms\ldb;

use manguto\cms\lib\Arquivos;
use manguto\cms\lib\ProcessResult;

class Database
{

    const filename = 'database/db.php';

    const fileIni = '<?php /*';

    const fileEnd = '*/ ?>';

    const operators = [
        '==',
        '!=',
        '<=',
        '<',        
        '>=',
        '>'        
    ];

    public $tables = array();

    public $info = array();

    public function __construct()
    {
        // echo "@@@";
        
        // verificacao de inicializacao
        if (! file_exists(self::filename)) {
            $this->save();
        }
        // carregamento
        $this->load();
    }

    private function table_verify(string $tablename)
    {
        $tablename = strtolower($tablename);
        if (! isset($this->tables[$tablename])) {
            $this->tables[$tablename] = new DatabaseTable($tablename);
        }
    }

    public function table_entries_array(string $tablename, array $filters = [])
    {
        $tablename = strtolower($tablename);
        $results = $this->getTableEntries($tablename, $filters);
        foreach ($results as &$r) {
            $r = $r->getData();
        }
        return $results;
    }

    /**
     * Retorna um array (de objetos ou de objetos convertidos em array)
     * @param string $tablename
     * @param string $conditions
     * @param bool $returnObjectAsArray
     * @return array
     */
    public function table_entries_array_advanced(string $tablename, string $conditions = '',bool $returnObjectAsArray=true):array
    {
        $tablename = strtolower($tablename);
        $this->table_verify($tablename);
        $return = array();
        foreach ($this->tables[$tablename]->entries as $entry) {
            //deb($entry);
            $condition = self::checkCondition($entry, $conditions);
            if ($condition) {
                //$entry = new Model();
                $return[$entry->getIdentifier()] = $entry;
            }
        }
        //verifica se os objetos obtidos deverao ser retornados como ARRAYS ou como OBJETOS
        if($returnObjectAsArray){
            foreach ($return as &$r) {
                if(false) $r = new Model();
                $r = $r->getData();
            }
        }
        
        return $return;
    }

    public function table_entries_amount(string $tablename)
    {
        $tablename = strtolower($tablename);
        
        $this->table_verify($tablename);
        
        $amount = sizeof($this->tables[$tablename]->entries);
        
        return $amount;
    }

    static public function table_entry_shape(Model $model): Model
    {
        $db = new Database();
        
        $tablename = strtolower($model->getModelName());
        
        $db->table_verify($tablename);
        
        $model = $db->tables[$tablename]->columns_update($model);
        
        return $model;
    }

    public function table_entry_save(Model &$model)
    {
        $tablename = $model->getModelName();
        
        $tablename = strtolower($tablename);
        
        $this->table_verify($tablename);
        
        $this->tables[$tablename]->entry_insert_update($model);
        
        $this->save();
        
        return $model;
    }

    public function table_entry_delete(Model &$model)
    {
        $tablename = $model->getModelName();
        
        $tablename = strtolower($tablename);
        
        $this->table_verify($tablename);
        
        $this->tables[$tablename]->entry_delete($model);
        
        $this->save();
        
        return $model;
    }

    public function table_column_update(Model $model, string $columnName, array $updates)
    {
        $tablename = strtolower($model->getModelName());
        
        $this->table_verify($tablename);
        
        // $this->tables[$tablename]->column_update($columnName, $updates); //1811
        
        $this->tables[$tablename]->columns_update($model, $columnName, $updates);
        
        $this->save();
    }

    public function table_column_remove(Model $model, string $columnName)
    {
        $tablename = strtolower($model->getModelName());
        
        $this->table_verify($tablename);
        
        $this->tables[$tablename]->column_remove($columnName);
        
        $this->save();
    }

    public function table_column_list(Model &$model)
    {
        $tablename = $model->getModelName();
        
        $tablename = strtolower($tablename);
        
        $this->table_verify($tablename);
        
        $table = $this->tables[$tablename];
        // if(false) $table = new DatabaseTable($tablename);
        
        return $table->columns;
    }

    private function save()
    {
        $data = self::fileIni . serialize($this) . self::fileEnd;
        Arquivos::escreverConteudo(self::filename, $data);
    }

    private function load()
    {
        $contentSerializedWrapped = Arquivos::obterConteudo(self::filename);
        $contentSerializedWrapped = str_replace(self::fileIni, '', $contentSerializedWrapped);
        $contentSerializedWrapped = str_replace(self::fileEnd, '', $contentSerializedWrapped);
        $contentSerialized = trim($contentSerializedWrapped);
        
        // deb(get_declared_classes());
        
        $content = unserialize($contentSerialized);
        if (isset($content->tables) && isset($content->tables)) {
            $this->info = $content->info;
            $this->tables = $content->tables;
        } else {
            throw new \Exception("Conteúdo do arquivo fonte da base de dados corrompido.");
        }
    }

    public function getTableEntries(string $tablename, array $filter = [])
    {
        $tablename = strtolower($tablename);
        
        // echo "<h1 style='background:red;'>SELECT</h1>";
        // deb($tablename,0); deb($filter,0);
        $this->table_verify($tablename);
        
        // deb($this->tables);
        
        $return = array();
        foreach ($this->tables[$tablename]->entries as $entry) {
            $accepted = true;
            // echo "<hr />";
            // deb($entry,0);
            foreach ($filter as $filterAttrName => $filterAttrValue) {
                
                $entryAttrValue = trim($entry->{'get' . $filterAttrName}());
                $filterAttrValue = trim($filterAttrValue);
                if ($entryAttrValue != $filterAttrValue) {
                    $accepted = false;
                }
            }
            if ($accepted) {
                $return[] = $entry;
            }
        }
        return $return;
    }

    private static function checkCondition(Model $entry, string $conditions)
    {
        if (trim($conditions) != '') {
            
            {
                // substituicoes
                {
                    // espacamentos
                    $spc = ' ';
                    $conditions = str_replace('&&', ' && ', $conditions);
                    $conditions = str_replace('||', ' || ', $conditions);
                    foreach (Database::operators as $op) {
                        $search = $op;
                        $replace = $spc.$op.$spc;                                                
                        $conditions = str_replace($search, $replace, $conditions);                        
                    }
                    //deb($conditions,0);
                    {
                        // ajuste de erros na substituicao
                        $conditions = str_replace('<'.$spc.'=', '<=', $conditions);
                        $conditions = str_replace('>'.$spc.'=', '>=', $conditions);
                    }
                    //deb($conditions,0);
                }
                // remocao espacamentos duplicados
                $conditions = preg_replace('!\s+!', ' ', $conditions);
            }
            
            
            {
                // analises
                // palavras
                $conditions_words = explode($spc, $conditions);
                //deb($conditions_words,0);
                { // substituicao das colunas envolvidas pelos respectivos valores
                    foreach ($conditions_words as $index => $word) {
                        $word = trim($word);
                        if (in_array($word, self::operators)) {
                            $operator = $word;
                            if (! isset($conditions_words[$index - 1])) {
                                throw new \Exception("Não foi encontrada a coluna correspondente para comparação ('$conditions' => '$word').");
                            } else {
                                $colunaNome = $conditions_words[$index - 1];
                                $colunaValor = $entry->{'get' . $colunaNome}();
                                
                                // verifica se a comparacao se dara entre strings
                                if (in_array($operator, [
                                    '==',
                                    '!='
                                ])) {
                                    $colunaValor = "'" . $colunaValor . "'";
                                } else {
                                    $colunaValor = floatval($colunaValor);
                                }
                                $conditions_words[$index - 1] = $colunaValor;
                            }
                        }
                    }
                }
            }
            
            $conditions_replaced = '$return = ( ' . implode(' ', $conditions_words) . ' );';
            //deb($conditions_replaced,0);
            eval($conditions_replaced);
            // deb($return);
        } else {
            $return = true;
        }
        return $return;
    }

    public function table_delete(string $tablename)
    {
        $tablename = strtolower($tablename);
        
        if (isset($this->tables[$tablename])) {
            unset($this->tables[$tablename]);
            $this->save();
        }
    }

    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###################################### SHOW ###############################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    // ###########################################################################################################
    static function ManagerOperations($tablename)
    {
        $_target = isset($_POST['_target']) ? trim($_POST['_target']) : false;
        $_option = isset($_POST['_option']) ? trim($_POST['_option']) : false;
        $_parameter = isset($_POST['_parameter']) ? trim($_POST['_parameter']) : false;
        // deb($_POST,0);
        { // ------------------------------------------------------------------------------- table
            if ($tablename != '' && $_target == 'table' && $_option == 'add' && $_parameter == '') {
                $database = new Database();
                $database->table_verify($tablename);
                $database->save();
                ProcessResult::setSuccess("Tabela adicionada com sucesso!");
                header('Location:' . $_SERVER['REQUEST_URI']);
                exit();
            }
            if ($tablename != '' && $_target == 'table' && $_option == 'save' && $_parameter == '') {
                $database = new Database();
                $database->table_verify($tablename);
                $database->save();
                ProcessResult::setSuccess("Tabela atualizada com sucesso!");
                header('Location:' . $_SERVER['REQUEST_URI']);
                exit();
            }
            if ($tablename != '' && $_target == 'table' && $_option == 'delete' && $_parameter == '') {
                $database = new Database();
                $database->table_delete($tablename);
                ProcessResult::setSuccess("Tabela removida com sucesso!");
                header('Location:' . self::url_back());
                exit();
            }
        }
        
        { // ------------------------------------------------------------------------------- table entry
            if ($tablename != '' && $_target == 'entry' && $_option == 'add' && $_parameter == '0') {
                self::verifyModelClass($tablename);
                $tablename = 'lib\model\\' . $tablename;
                // if(false)$newEntry=new Model();
                $newEntry = new $tablename();
                $newEntry->save();
                ProcessResult::setSuccess("Registro adicionado com sucesso!");
                {
                    // #########################################################################
                    // ########### REDIRECIONAMENTO PARA MODO DE EDICAO DIRETO #################
                    // #########################################################################
                    $_POST['_target'] = 'entry';
                    $_POST['_option'] = 'edit';
                    $_POST['_parameter'] = $newEntry->getIdentifier();
                    // #########################################################################
                    // #########################################################################
                    // #########################################################################
                }
            }
            if ($tablename != '' && $_target == 'entry' && $_option == 'save' && $_parameter != '') {
                self::verifyModelClass($tablename);
                $database = new Database();
                $className = 'lib\model\\' . $tablename;
                // if(false) $model = new Model();
                $model = new $className($_parameter);
                // deb($_POST,0);
                { // clear not needed post _parameters
                    unset($_POST['_target']);
                    unset($_POST['_parameter']);
                    unset($_POST['_option']);
                }
                // deb($_POST,0);
                $model->setData($_POST);
                // deb($model);
                $model->save();
                ProcessResult::setSuccess("Registro atualizado com sucesso!");
                header('Location:' . self::url_back() . '/' . $tablename);
                exit();
            }
            if ($tablename != '' && $_target == 'entry' && $_option == 'delete' && $_parameter != '0') {
                $tablename = 'lib\model\\' . $tablename;
                $newEntry = new $tablename($_parameter);
                $newEntry->delete();
                ProcessResult::setSuccess("Registro removido com sucesso!");
            }
        }
        
        { // ------------------------------------------------------------------------------- table info
          // --- add
            if ($tablename != '' && $_target == 'info' && $_option == 'add' && $_parameter != '') {
                self::verifyModelClass($tablename);
                { // adicao de coluna através de atualizacao em objeto temporario
                    $className = 'lib\model\\' . $tablename;
                    $method = 'set' . $_parameter;
                    
                    $temp = new $className();
                    $temp->$method('');
                    $temp->save();
                    $temp->delete();
                }
                ProcessResult::setSuccess("Coluna adicionada com sucesso!");
                {
                    // #########################################################################
                    // ########### REDIRECIONAMENTO PARA MODO DE EDICAO DIRETO #################
                    // #########################################################################
                    $_POST['_target'] = 'info';
                    $_POST['_option'] = 'edit';
                    $_POST['_parameter'] = $_parameter;
                    // #########################################################################
                    // #########################################################################
                    // #########################################################################
                }
                // header('Location:' . self::url_back() . '/' . $tablename);
                // exit();
            }
            
            // --- update
            if ($tablename != '' && $_target == 'info' && $_option == 'save' && $_parameter != '') {
                // deb("save info $_parameter",0);
                
                $database = new Database();
                $className = 'lib\model\\' . $tablename;
                $model = new $className();
                $database->table_column_update($model, $_parameter, [
                    'type' => $_POST['type'],
                    'default' => $_POST['default']
                ]);
                ProcessResult::setSuccess("Coluna atualizada com sucesso!");
                header('Location:' . self::url_back() . '/' . $tablename);
                exit();
            }
            // --- delete
            if ($tablename != '' && $_target == 'info' && $_option == 'delete' && $_parameter != '') {
                // deb($_POST);
                $database = new Database();
                $database->table_verify($tablename);
                $tablename = 'lib\model\\' . $tablename;
                $model = new $tablename();
                ProcessResult::setSuccess("Coluna removida com sucesso!");
                $database->table_column_remove($model, $_parameter);
            }
        }
    }

    static private function verifyModelClass($tablename)
    {
        $filename = "lib/model/" . ucfirst($tablename) . ".php";
        if (! file_exists($filename)) {
            $data = '<?php

namespace lib\model;

use manguto\cms\ldb\Model;

class ' . ucfirst($tablename) . ' extends Model
{
        
}

?>';
            file_put_contents($filename, $data);
        }
    }

    static function Manager(string $tablename = '', string $style = '')
    {
        // tablenames to lowercase
        $tablename = strtolower($tablename);
        
        { // OPERATIONS
            self::ManagerOperations($tablename);
        }
        
        $return = array();
        
        { // JAVASCRIPT
            
            $return[] = "<script>
            
            function tableSetTarget(value){
                $('#table input[name=\"_target\"]').val(value);
            }
            function tableSetOption(value){
                $('#table input[name=\"_option\"]').val(value);
            }
            function tableSetParameter(value){
                $('#table input[name=\"_parameter\"]').val(value);
            }
            function tableSetAction(new_tablename){
                var action = $('#table').attr('action');
                action = action + '/' + new_tablename
                $('#table').attr('action',action);            
            }
            function tableSubmit(){
                $('#table').submit();
            }
            
            //--------------------------------------------- table control
            function table_new(){
                var tableName = prompt('Digite o nome da nova tabela:');                
                if (tableName != null) {
                    tableSetTarget('table');
                    tableSetOption('add');
                    tableSetParameter('');
                    tableSetAction(tableName);
                    tableSubmit();
                }
            }
            
            $(document).ready(function(){
            
            });
            
            </script>";
        }
        
        { // --- FORMULARIO
            $action = Database::form_action();
            $return[] = "<form id='table' method='post' action='$action'>";
            $return[] = "<input type='hidden' name='_target' value=''>";
            $return[] = "<input type='hidden' name='_option' value=''>";
            $return[] = "<input type='hidden' name='_parameter' value=''>";
            $return[] = "</form>";
        }
        
        if ($tablename == '') {
            $return[] = "<br/>";
            $return[] = "<h3>Tabelas do Sistema</h3>";
            $return[] = "<br/>";
            $database = new Database();
            foreach ($database->tables as $table) {
                // checks filter
                if (false)
                    $table = new DatabaseTable('teste');
                $href = Database::form_action() . '/' . $table->name;
                $return[] = "<a class='btn btn-danger btn-sm float-left'href='$href'>" . strtoupper($table->name) . "</a><br/><br/>";
            }
            $return[] = "<br/><button class='btn btn-success btn-sm float-left' onclick='table_new()' title='Adicionar/Criar nova tabela'>";
            $return[] = "<img src='/res/general/images/icons/plus-2x.png'/>&nbsp;&nbsp;&nbsp;ADICIONAR";
            $return[] = "</button><br/><br/>";
        } else {
            
            $database = new Database();
            if (isset($database->tables[$tablename])) {
                $table = $database->tables[$tablename];
                $return[] = $table->Manager($style);
            } else {
                throw new \Exception("Tabela não encontrada no Banco de Dados ('$tablename').");
            }
        }
        
        return implode('', $return);
    }

    static function form_action()
    {
        $request_uri = str_replace('\\', DIRECTORY_SEPARATOR, $_SERVER['REQUEST_URI']);
        $request_uri = str_replace('/', DIRECTORY_SEPARATOR, $request_uri);
        $action = explode(DIRECTORY_SEPARATOR, $request_uri);
        array_shift($action);
        array_shift($action);
        $action = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $action);
        return $action;
    }

    static function url_back()
    {
        $request_uri = str_replace('\\', DIRECTORY_SEPARATOR, $_SERVER['REQUEST_URI']);
        $request_uri = str_replace('/', DIRECTORY_SEPARATOR, $request_uri);
        $action = explode(DIRECTORY_SEPARATOR, $request_uri);
        array_shift($action);
        array_pop($action);
        $action = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $action);
        return $action;
    }
}

?>