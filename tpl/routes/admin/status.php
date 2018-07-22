<?php
use manguto\cms\gms\PageAdmin;
use lib\model\Testes;
use manguto\cms\lib\Arrays;
use manguto\cms\ldb\Database;
use manguto\cms\lib\ProcessResult;


// ---------------------------------------------- status
$app->get('/admin/status', function () {
    
    //User::verifyLogin(); //ATIVAR 
    
    $Session = serialize(Arrays::arrayShow($_SESSION, '_SESSION'));
    
    $page = new PageAdmin();
    $page->setTpl("status", [
        'Session' => $Session
    ]);
});

$app->get('/admin/status/session/reset', function () {
    //User::verifyLogin(); //ATIVAR
    
    session_destroy();
    session_start();
    
    headerLocation('/admin/status');
    exit();
});



// ---------------------------------------------- testes
$app->get('/admin/testes', function () {
    //User::verifyLogin(); //ATIVAR
    
    $testes = Testes::Iniciar();
    
    $page = new PageAdmin();
    $page->setTpl("testes", [
        'title' => "Web Code Help",
        'testes' => serialize($testes)
    ]);
});

?>