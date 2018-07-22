<?php
use manguto\cms\gms\PageAdmin;
use lib\model\User;

// ---------------------------------------------- main
$app->get('/admin', function () {
    User::verifyLogin();  
    
    /*ProcessResult::setSuccess("Teste de SUCESSO!");
    ProcessResult::setError("Teste de ERRO!");
    ProcessResult::setWarning("Teste de AVISO!");/**/
    
    
    $page = new PageAdmin();    
    $page->setTpl("index");
});


?>