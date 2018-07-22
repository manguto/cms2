<?php
use manguto\cms\ldb\Database;
use manguto\cms\gms\PageAdmin;
use lib\model\User;
use manguto\cms\lib\ProcessResult;


// ===========================================================================================

$app->get('/admin/users', function () {
    
    User::verifyLogin();
    
    $database = new Database();
    
    $users = $database->table_entries_array('user');
    
    $page = new PageAdmin();
    
    $page->setTpl("users", [
        'users' => $users
    ]);
});

// ===========================================================================================

$app->get('/admin/users/create', function () {    
    User::verifyLogin();
    
    $page = new PageAdmin();
    $page->setTpl("users-create", [
        'temp' => 'usuario' . date("is")                
    ]);
}); 

// -------------------------------------------------------------------------------

$app->post('/admin/users/create', function () {
    
    User::verifyLogin();
    
    // fix - form inadmin (checkbox)
    $_POST['inadmin'] = ! isset($_POST['inadmin']) ? 0 : 1; 
    
    // password crypt
    $_POST['password'] = User::password_crypt($_POST['password']);
    
    try {
        
        $user = new User();        
        $user->setData($_POST);
        $user->verifyFieldsToCreateUpdate();        
        $user->save();
        ProcessResult::setSuccess("Usuário salvo com sucesso!");
        headerLocation("/admin/users");
        exit();
        
    } catch (Exception $e) {
        
        ProcessResult::setError($e->getMessage());
        headerLocation("/admin/users/create");
        exit();
    }
    
    
});

// ===========================================================================================

$app->get('/admin/users/:userid/delete', function ($userid) {
    
    User::verifyLogin();
    
    $user = new User($userid);
    // deb($user);
    $user->delete();
    
    ProcessResult::setSuccess("Usuário removido com sucesso!");
    headerLocation("/admin/users");
    exit();
});

// ===========================================================================================

$app->get('/admin/users/:userid', function ($userid) {
    
    User::verifyLogin();
    
    $user = new User($userid);
    // deb($user);
    $page = new PageAdmin();
    $page->setTpl("users-update", [
        'user' => $user->getData()
    ]);
});

// -------------------------------------------------------------------------------

$app->post('/admin/users/:userid', function ($userid) {
    
    User::verifyLogin();
        
    // fix - form inadmin (checkbox)
    $_POST['inadmin'] = ! isset($_POST['inadmin']) ? 0 : 1;
    
    try {
        
        $user = new User($userid);
        $user->setData($_POST);
        $user->verifyFieldsToCreateUpdate();
        $user->save();
        ProcessResult::setSuccess("Usuário atualizado com sucesso!");
        headerLocation("/admin/users");
        exit();
        
    } catch (Exception $e) {
        
        ProcessResult::setError($e->getMessage());
        headerLocation("/admin/users/create");
        exit();
    }
    
});
?>