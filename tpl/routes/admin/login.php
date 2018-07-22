<?php
use manguto\cms\gms\PageAdmin;
use manguto\cms\lib\ProcessResult;
use lib\model\User;

// ---------------------------------------------- formulario
$app->get('/admin/login', function () {
    
    $page = new PageAdmin();
    
    $page->setTpl("login");
});
// ---------------------------------------------- validacao
$app->post('/admin/login', function () {
    
    try{
        User::login($_POST['login'], $_POST['password']);
        
        if(checkUserLoggedAdmin()){
            headerLocation('/admin');
        }else{
            //nao eh permitido o login de usuario nao admin através deste formulario
            ProcessResult::setError("O login de usuários padrão, deve ser realizado através do formulário abaixo.");
            
            User::logout();
            headerLocation('/login');
        }        
        
    }catch (Exception $e){
        ProcessResult::setError($e->getMessage());
        headerLocation('/admin/login');
    }
    
    exit();
});
// ---------------------------------------------- logout
$app->get('/admin/logout', function () {
    
    User::logout();
    
    headerLocation("/admin/login");
    
    exit();
});

?>