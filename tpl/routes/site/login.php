<?php
use manguto\cms\gms\Page;
use manguto\cms\lib\ProcessResult;
use lib\model\User;

// ===========================================================================================

$app->get('/login', function () {
    
    $page = new Page();
    
    if (isset($_SESSION[SIS_ABREV]['registerFormValues'])) {
        $registerFormValues = $_SESSION[SIS_ABREV]['registerFormValues'];
        unset($_SESSION[SIS_ABREV]['registerFormValues']);
    } else {
        $registerFormValues = [
            'name' => '',
            'email' => '',
            'phone' => ''
        ];
    }
    $page->setTpl("login", [
        'registerFormValues' => $registerFormValues
    ]);
});
// ------------------------------------------------------------------------------
$app->post('/login', function () {
    
    // deb($_POST);
    
    try {
        User::login($_POST['login'], $_POST['password']);
        
        if (checkUserLoggedAdmin()) {
            headerLocation('/admin');
            exit();
        } else {
            headerLocation('/');
            exit();
        }
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/login');
        exit();
    }
});

// --------------------------------------------------------------------------------
$app->post('/register', function () {
    // deb($_POST);
    
    throw new Exception("A criação de novos usuários está desabilitada até segunda orde. Obrigado!");
    
    // -------------montagem do usuario
    $user = new User();
    
    $user->setData([
        'inadmin' => 0,
        'name' => $_POST['name'],
        'login' => $_POST['email'],
        'email' => $_POST['email'],
        'password' => User::password_crypt($_POST['password']),
        'phone' => $_POST['phone']
    ]);
    // deb($user,0);
    
    // ------------- verificacao de parametros enviados
    
    try {
        $user->verifyFieldsToCreateUpdate();
        $user->save();
        ProcessResult::setSuccess("Cadastro realizado com sucesso!<br/>Seja bem vindo(a) à nossa plataforma!!");
        
        User::login($_POST['email'], $_POST['password']);
        headerLocation('/');
        exit(); 
        
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/login');
        exit();
    }    
    
});

// ===========================================================================================
$app->get('/logout', function () {
    
    User::logout();
    
    headerLocation("/login");
    
    exit();
});

?>