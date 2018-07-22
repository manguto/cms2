<?php
use manguto\cms\gms\PageAdmin;
use lib\model\User;
use manguto\cms\lib\ProcessResult;

// =================================================================================

$app->get('/admin/profile', function () {
    
    User::verifyLogin(false);
    
    $user = User::getSessionUser();
    //deb($user);
    
    $page = new PageAdmin();
    $page->setTpl("profile", [
        'user' => $user->getData(),
        'form_action'=>'/admin/profile',
        'link_change_password'=>'/admin/profile/change-password'
    ]);
});
// ----------------------------------------------

$app->post('/admin/profile', function () {
    
    
    User::verifyLogin(false);
    
    $user = User::getSessionUser();
    
    { // --- PARAMETERS VERIFICATION & CERTIFICATION
        $_POST['inadmin'] = $user->getInadmin();
        $_POST['password'] = $user->getPassword();        
        if(checkUserLoggedAdmin()===false){
            $_POST['login'] = $_POST['email'];
        }        
    }
    
    $user->setData($_POST);
    
    try {
        $user->verifyFieldsToCreateUpdate();
        $user->save();
        ProcessResult::setSuccess('Usuário salvo com sucesso!');
        headerLocation('/admin/profile');
        exit();
        
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        $_SESSION[SIS_ABREV]['registerFormValues'] = $_POST;
        headerLocation('/admin/profile');
        exit();
    }    
    
});

    // =================================================================================

$app->get('/admin/profile/change-password', function () {
    
    User::verifyLogin(false);
    
    $user = User::getSessionUser();
    // deb($user);
    
    $page = new PageAdmin();
    $page->setTpl("profile-change-password",[
        'form_action'=>'/admin/profile/change-password'
    ]);
});
// ----------------------------------------------

$app->post('/admin/profile/change-password', function () {
    
    User::verifyLogin(false);
    
    $user = User::getSessionUser();
    
    try {
        
        $current_pass = isset($_POST['current_pass']) ? $_POST['current_pass'] : '';
        $new_pass = isset($_POST['new_pass']) ? $_POST['new_pass'] : '';
        $new_pass_confirm = isset($_POST['new_pass_confirm']) ? $_POST['new_pass_confirm'] : '';
        $user->verifyPasswordUpdate($current_pass, $new_pass, $new_pass_confirm);
        
        $user->setPassword(User::password_crypt($new_pass));
        
        $user->save();
        
        ProcessResult::setSuccess('Senha alterada com sucesso!!!');
        headerLocation('/admin/profile');
        exit();
        
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/admin/profile/change-password');
        exit();
    }
    
    
});

