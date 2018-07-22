<?php
namespace manguto\cms\ldb;

class DatabaseTable
{

    public $name;

    public $identifierName;

    public $columns = array();

    public $lastIndex = 0;

    public $entries = array();

    // ----------------------------------------------------------------------------------------------------------------------------------
    public function __construct(string $name)
    {
        $this->name = strtolower($name);
        
        $this->identifierName = $this->name . 'id';
    }

    private function entry_get(int $identifierValue, bool $throwException = true): Model
    {
        if (isset($this->entries[$identifierValue])) {
            return $this->entries[$identifierValue];
        } else {
            if ($throwException) {
                throw new \Exception("Registro do(a) '" . $this->name . "' não encontrado(a) ($identifierValue).");
            } else {
                return false;
            }
        }
    }

    public function entry_insert_update(Model &$model)
    {
        
        $identificadorValor = $model->getIdentifier();
        
        if($identificadorValor==0){
            $model->setIdentifier(++ $this->lastIndex);            
        }else{
            if (!isset($this->entries[$model->getIdentifier()])) {
                $this->lastIndex = $identificadorValor+1;
            }
        }
        $this->columns_update($model);
        $this->entries[$model->getIdentifier()] = $model;
        
        /*
        if ($model->getIdentifier() == 0) {
            $model->setIdentifier(++ $this->lastIndex);
        } else {
            if (! isset($this->entries[$model->getIdentifier()])) {
                throw new \Exception("Registro '" . $model->getModelName() . "' não encontrado (" . $model->getIdentifier() . ").");
            }
        }
        $this->columns_update($model);
        $this->entries[$model->getIdentifier()] = $model;
        */
        
    }

    public function entry_delete(Model &$model)
    {
        unset($this->entries[$model->getIdentifier()]);
        
        $model->setIdentifier(0);
    }

    /**
     * Atualiza a informacao das colunas da tabela, bem como de todos os registros da mesma com base no MODELO passado
     * - $model => table[columns] (default)
     *
     *
     * Verifica se todos os registros da tabela possuem a coluna em questao e caso contrário, insere-a com o valor padrao da mesma
     * - $model => table[entries]
     * 
     * Adiciona e atualiza as informacoes sobre uma coluna da tabela
     * - $columnName & $updates => table[columns] (default OR personalized)
     *
     * Atualiza o modelo passado com as colunas da tabela a qual este está vinculado
     * - table[columns] => $model
     * 
     * @param Model $model
     */
    public function columns_update(Model $model,string $targetColumnName='',array $updates = [])
    {
        // atualiza a informacao das colunas desta tabela, bem como de todos os registros da mesma com base no MODELO passado
        $modelData = $model->getData();
        foreach ($modelData as $columnName => $columnValue) {
            if (! isset($this->columns[$columnName])) {
                
                //adiciona a coluna a base da tabela
                $this->column_update($columnName);
                
                // verifica se todos os registros da tabela possuem a coluna em questao e caso contrário, insere-a com o valor padrao da mesma
                foreach ($this->entries as &$entry) {
                    $entryData = $entry->getData();
                    if (! isset($entryData[$columnName])) {
                        $entry->{'set' . $columnName}($this->columns[$columnName]->default);
                    }
                }
            }
        }
        
        //adiciona ou atualiza as informacoes sobre uma coluna da tabela
        if($targetColumnName!=''){
            $this->column_update($targetColumnName,$updates);
        }        
        
        // atualiza o modelo passado com as colunas da tabela a qual este está vinculado
        $modelData = $model->getData();
        foreach ($this->columns as $column) {
            if (! isset($modelData[$column->name])) {
                $model->{'set' . $column->name}($column->default);
            }
        }        
        return $model;
    }

    
    /**
     * adiciona ou atualiza as informacoes sobre uma coluna da tabela 
     *
     * @param string $columnName
     * @param array $updates
     */
    private function column_update(string $columnName, array $updates = [])
    {
        if (! isset($this->columns[$columnName])) {
            $this->columns[$columnName] = new DatabaseColumn($columnName);
        }
        foreach ($updates as $columnNameTemp => $columnValueTemp) {
            $this->columns[$columnName]->$columnNameTemp = $columnValueTemp;
        }
    }
    
    /**
     * remove uma coluna da tabela e de seus registros   
     *
     * @param string $columnName
     * @param array $updates
     */
    public function column_remove(string $columnName)
    {
        if (isset($this->columns[$columnName])) {
            unset($this->columns[$columnName]);
        }
        $entries = $this->entries;
        foreach ($entries as $entry) {
            if(false) $entry = new Model();
            $entry->dataRemove($columnName);            
        }
    }
    
    
    
    

    // ########################################################################################### MANAGER
    // ########################################################################################### MANAGER
    // ########################################################################################### MANAGER
    public function Manager(string $style = '')
    {
        $return = array();
        
        // tablename
        $t = $this->name;
        
        // html javascript
        $return[] = "<script>

            function {$t}SetTarget(value){
                $('#$t input[name=\"_target\"]').val(value);
            }
            function {$t}SetOption(value){
                $('#$t input[name=\"_option\"]').val(value);
            }
            function {$t}SetParameter(value){
                $('#$t input[name=\"_parameter\"]').val(value);
            }
            function {$t}Submit(){    
                $('#$t').submit();
            }
            
            //--------------------------------------------- table entry control
            function {$t}_entry_add(){    
                {$t}SetTarget('entry');
                {$t}SetOption('add');
                {$t}SetParameter(0);
                {$t}Submit();
            }
            function {$t}_entry_edit(id){    
                {$t}SetTarget('entry');
                {$t}SetOption('edit');
                {$t}SetParameter(id);
                {$t}Submit();
            }
            function {$t}_entry_save(entryid){
                {$t}SetTarget('entry');
                {$t}SetOption('save');
                {$t}SetParameter(entryid);

                $('tr#{$t}_entry_edit input').each(function(){
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    $('form#$t').append('<input type=\"hidden\" name=\"'+name+'\" value=\"'+value+'\">');
                });

                {$t}Submit();
            }
            function {$t}_entry_delete(id){
                if(confirm('Deseja realmente excluir este(a) \"$t\"? [identificador='+id+']')){
                    {$t}SetTarget('entry');
                    {$t}SetOption('delete');
                    {$t}SetParameter(id);
                    {$t}Submit();
                }
            }
            //--------------------------------------------- table info control
            function {$t}_info_add(){
                var infoname = prompt('Digite o nome da nova coluna:');

                if (infoname != null) {
                    {$t}SetTarget('info');
                    {$t}SetOption('add');
                    {$t}SetParameter(infoname);
                    {$t}Submit();
                }
            }
            function {$t}_info_edit(infoname){
                {$t}SetTarget('info');
                {$t}SetOption('edit');
                {$t}SetParameter(infoname);
                {$t}Submit();            
            }
            function {$t}_info_save(infoname){
                {$t}SetTarget('info');
                {$t}SetOption('save');
                {$t}SetParameter(infoname);

                $('tr#{$t}_info_edit input').each(function(){
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    $('form#$t').append('<input type=\"hidden\" name=\"'+name+'\" value=\"'+value+'\">');
                });

                {$t}Submit();
            }
            function {$t}_info_delete(infoname){
                if(confirm('Deseja realmente excluir este o parametro \"'+infoname+'\" da tabela \"$t\"?')){
                    {$t}SetTarget('info');
                    {$t}SetOption('delete');
                    {$t}SetParameter(infoname);
                    {$t}Submit();
                }
            }
            //--------------------------------------------- table control
            function {$t}_delete(){
                if(confirm('CUIDADO!!!! Tem certeza que deseja excluir a tabela \"$t\"?')){
                    {$t}SetTarget('table');
                    {$t}SetOption('delete');
                    {$t}SetParameter('');
                    {$t}Submit();
                }
            }
            
            $(document).ready(function(){
            
            });
            
            </script>";
        
        { // ======================================================== html form options control
          
            // form action
            $action = Database::form_action();
            
            $return[] = "<form id='$t' method='post' action='$action'>";
            $return[] = "<input type='hidden' name='_target' value=''>";
            $return[] = "<input type='hidden' name='_option' value=''>";
            $return[] = "<input type='hidden' name='_parameter' value=''>";
            $return[] = "</form>";
        }
        
        if (isset($_POST['_target']) && $_POST['_target'] == 'entry' && $_POST['_option'] == 'edit' && $_POST['_parameter'] != '') {
            $entryEditionMode = true;
        } else {
            $entryEditionMode = false;
        }
        
        $return[] = "<table style='$style overflow:hidden; font-size:11px;' class='table table-sm w-100'>";
        
        $return[] = "<tr><td>";
        $return[] = "<div class='h4 float-left w-50'><a href='$action' title='Atualizar informações'>" . $this->name . "</a></div>";
        $return[] = "<div class='h4 float-right w-25'>";
        $return[] = "<button onclick='{$t}_entry_add()' class='btn btn-success btn-sm float-right m-1'title='Adicionar Registro'>";
        $return[] = "<img src='/res/general/images/icons/plus-2x.png'/>";
        $return[] = "</button>";
        $return[] = "</div>";
        $return[] = "</td></tr>";
        
        { 
            // ############################################################################################################################# content
            // ############################################################################################################################# content
            // ############################################################################################################################# content
            // ############################################################################################################################# content
            // ############################################################################################################################# content
            // ############################################################################################################################# content
            // ############################################################################################################################# content
            // ############################################################################################################################# content
            // ############################################################################################################################# content
            $content = array();
            $content[0] = "<table style='$style' class='table table-striped table-bordered database_scenario'>";
            $columns = array();
            if (sizeof($this->entries) > 0) {
                
                { // --- verificacao de edicao de informacao
                    if ($entryEditionMode) {
                        $entryEditionId = trim($_POST['_parameter']);
                    } else {
                        $entryEditionId = false;
                    }
                }
                
                foreach ($this->entries as $entry) {
                    
                    if (false)
                        $entry = new Model();
                    if (sizeof($content) == 1) {
                        $content[] = '<thead class="thead-dark">';
                        $content[] = '<tr>';
                        foreach ($entry->getData() as $k => $v) {
                            $columns[] = $k;
                            $content[] = "<th scope='col' class='text-monospace font-weight-normal'>$k</th>";
                        }
                        $content[] = "<th scope='col' class='text-monospace font-weight-normal' title='Opções' style='width:100px;'>Opções</th>";
                        $content[] = '</tr>';
                        $content[] = '</thead>';
                        $content[] = '<tbody>';
                    }
                    
                    // if(false)$column = new DatabaseColumn($name);
                    if ($entry->getIdentifier() == $entryEditionId) {
                        $entryEdit = true;
                        $content[] = "<tr id='{$t}_entry_edit' style='background:#ff0;'>";
                        
                    } else {
                        $entryEdit = false;
                        $content[] = "<tr>";                        
                    }
                    
                    
                    //permite o foco automatico do mprimeiro campo em edicao
                    $autofocus = true;
                    foreach ($columns as $index=>$columnName) {
                        
                        $content[] = "<td scope='row' class='text-monospace' title='$columnName'>";
                        
                        if ($entryEdit) {
                            //ocultar edicao de campo id                            
                            if(substr($columnName, -2,2)!='id' || $index!=0){                                
                                $autofocus = $autofocus===true ? 'autofocus' : '';                                
                                $content[] = "<input type='text' class='form-control' $autofocus name='$columnName' value='" . $entry->{'get' . $columnName}() . "'>";
                            }else{                                
                                $content[] = $entry->{'get' . $columnName}();                                
                            }                            
                        } else {
                            $content[] = $entry->{'get' . $columnName}();
                        }
                        
                        $content[] = "</td>";
                    }
                    
                    {//options
                        $content[] = "<td scope='row' class='text-monospace' style='font-size:10px;'>";
                        
                        $content[] = "<button onclick='{$t}_entry_delete(" . $entry->getIdentifier() . ")' class='btn btn-danger btn-sm float-right m-1' title='Excluir Registro' style='font-size:10px;'>";
                        $content[] = "<img src='/res/general/images/icons/trash-2x.png'/>";
                        $content[] = "</button>";
                        
                        if ($entryEdit) {
                            $content[] = "<button onclick='{$t}_entry_save(" . $entry->getIdentifier() . ")' class='btn btn-success btn-sm float-right m-1' title='Salvar Registro' style='font-size:10px;'>";
                            $content[] = "<img src='/res/general/images/icons/circle-check-2x.png'/>";
                            $content[] = "</button>";
                        } else {
                            $content[] = "<button onclick='{$t}_entry_edit(" . $entry->getIdentifier() . ")' class='btn btn-warning btn-sm float-right m-1' title='Editar Registro' style='font-size:10px;'>";
                            $content[] = "<img src='/res/general/images/icons/pencil-2x.png'/>";
                            $content[] = "</button>";
                        }
                        
                        
                        $content[] = "</td>";
                    }   
                    
                    $content[] = '</tr>';
                }
            } else {
                $content[] = '<tr><td>Nenhum registro cadastrado.</td></tr>';
            }
            $content[] = '</tbody>';
            $content[] = '</table>';
        }
        
        { 
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            // ########################################################################################################################### info
            $info = array();
            
            if (isset($_POST['_target']) && $_POST['_target'] == 'info' && $_POST['_option'] == 'edit' && $_POST['_parameter'] != '') {
                $infoEditionMode = true;
            } else {
                $infoEditionMode = false;
            }
            
            $unicTerm = microtime();
            $unicTerm = str_replace('.', '', $unicTerm);
            $unicTerm = str_replace(' ', '', $unicTerm);
            $table_info_id = $this->name . '_info_' . $unicTerm;
            
            $columnQuant = sizeof($this->columns);
            
            $info[] = "<a href='javascript:$(\"#$table_info_id\").toggle();' class='btn btn-light btn-sm float-right m-1' style='font-size:10px;'>Informações</a>";
            
            { // exibicao quando da edicao
                if ($infoEditionMode) {
                    $display = 'block';
                } else {
                    $display = 'none';
                }
            }
            
            $info[] = "<table id='$table_info_id' style='display:$display; $style' class='table database_scenario table-bordered table-striped mt-4 w-50'>";
            $info[] = "<thead>";
            $info[] = "<tr>";
            $info[] = "<td scope='row' colspan='4'>";
            $info[] = "<button onclick='{$t}_info_add()' class='btn btn-success btn-sm float-right m-1' title='Adicionar Coluna'>";
            $info[] = "<img src='/res/general/images/icons/plus.png'/>";
            $info[] = "</button>";
            $info[] = "</td>";
            $info[] = "</tr>";
            
            $sizeInfoHead = sizeof($info);
            if ($columnQuant == 0) {
                $info[] = "</thead>";
                $info[] = '<tr><td class="text-monospace">Nenhuma coluna cadastrada.</td></tr>';
                $columnQuant = 1;
            } else {
                
                { // --- verificacao de edicao de informacao
                    if ($infoEditionMode) {
                        $columnAttrValueEdit = trim($_POST['_parameter']);
                    } else {
                        $columnAttrValueEdit = false;
                    }
                }
                
                foreach ($this->columns as $column) {
                    if (sizeof($info) == $sizeInfoHead) {
                        $info[] = "<tr>";
                        foreach ($column as $columnAttrName => $columnAttrValue) {
                            $info[] = "<th scope='col' class='text-monospace'>$columnAttrName</th>";
                        }
                        $info[] = "<th scope='col' class='text-monospace' style='width:100px;'>Opções</th>";
                        $info[] = "</tr>";
                        $info[] = "</thead>";
                        $info[] = "<tbody>";
                    }
                    
                    // if(false)$column = new DatabaseColumn($name);
                    if ($column->name == $columnAttrValueEdit) {
                        $infoEdit = true;
                        $info[] = "<tr id='{$t}_info_edit'>";
                    } else {
                        $infoEdit = false;
                        $info[] = "<tr>";
                    }
                    
                    foreach ($column as $columnAttrName => $columnAttrValue) {
                        
                        $info[] = "<td scope='row' class='text-monospace' title='$columnAttrName'>";
                        
                        if ($infoEdit) {
                            if($columnAttrName=='name'){
                                $info[] = $columnAttrValue;
                            }else{
                                $autofocus = $autofocus===true ? 'autofocus' : '';
                                $info[] = "<input type='text' $autofocus class='form-control' name='$columnAttrName' value='$columnAttrValue'>";
                            }                           
                        } else {
                            $info[] = $columnAttrValue;
                        }
                        $info[] = "</td>";
                    }
                    
                    {//options
                        $info[] = "<td scope='row' class='text-monospace text-right'>";
                        if ($infoEdit) {
                            $info[] = "<button onclick='{$t}_info_save(\"" . $column->name . "\")' class='btn btn-success btn-sm m-1' title='Salvar Coluna'><img src='/res/general/images/icons/circle-check.png'/></button>";
                        } else {
                            $info[] = "<button onclick='{$t}_info_edit(\"" . $column->name . "\")' class='btn btn-warning btn-sm m-1' title='Editar Coluna'><img src='/res/general/images/icons/pencil.png'/></button>";
                        }
                        $info[] = "<button onclick='{$t}_info_delete(\"" . $column->name . "\")' class='btn btn-danger btn-sm m-1' title='Excluir Coluna'><img src='/res/general/images/icons/trash.png'/></button>";
                        $info[] = "</td>";
                    }                    
                    
                    if ($infoEdit) {
                        $info[] = "</form>";
                    }
                    
                    $info[] = "</tr>";
                }
            }
            
            $info[] = "<tr><td scope='row' colspan='4' class='text-right'>Índice: " . $this->lastIndex . "</td></tr>";
            $info[] = "<tr><td scope='row' colspan='4' class='text-right'><button onclick='{$t}_delete()' title='EXCLUIR TABELA' class='btn btn-danger btn-sm' style='font-size:9px;'><img src='/res/general/images/icons/warning-2x.png'/> EXCLUIR TABELA</td></tr>";
            $info[] = "</tbody>";
            $info[] = "</table>";
        }
        // content
        $return[] = "<tr><td>" . implode(chr(10), $content) . "</td></tr>";
        // info
        $return[] = "<tr><td>" . implode(chr(10), $info) . "</td></tr>";
        
        $return[] = "</table>";
        
        return implode(chr(10), $return);
    }
}

?>  