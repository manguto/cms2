<?php
use manguto\cms\gms\Page;
use manguto\cms\lib\ProcessResult;
use lib\model\User;

// ===========================================================================================
$app->get('/forgot', function () {    
    $page = new Page();
    $page->setTpl("forgot", [
        'form_action' => '/forgot'
    ]);
});
// --------------------------------------------------------------------------
$app->post("/forgot", function () {
    // deb($_POST);
    try {
        $user = User::getForgot(trim($_POST['email']), false);
        headerLocation('/forgot/sent');
        exit();
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/forgot');        
        exit();
    }
});
// ===========================================================================================
$app->get('/forgot/sent', function () {
    
    $page = new Page();
    
    $email = User::getForgotEmail();
    $emailInfo = explode('@', $email);
    $emailUrl = $emailInfo[1];
    $emailInfo2 = explode('.', $emailUrl);
    $emailName = ucfirst($emailInfo2[0]);
    
    $page->setTpl("forgot-sent", [
        'email' => $email,
        'emailUrl' => 'http://' . $emailUrl,
        'emailName' => $emailName
    ]);
});
// ===========================================================================================
$app->get('/forgot/reset', function () {
    
    $code = $_GET['code'];
    
    try {
        $user = User::validForgotDecrypt($code);
        $page = new Page();
        $page->setTpl("forgot-reset", [
            'form_action' => '/forgot/reset',
            'name' => $user->getname(),
            'code' => $code
        ]);
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/forgot/reset');
        exit();
    }
});
// -------------------------------------------------------------------------
$app->post('/forgot/reset', function () {
    
    $code = $_POST['code'];
    
    try {
        $user = User::validForgotDecrypt($code);
        User::setForgotUsed($user->getrecoveryid());
        $user->setPassword(User::password_crypt($_POST['password']));
        $user->save();
        $page = new Page();
        $page->setTpl("forgot-reset-success",[
            'link_form_login'=>'/login'
        ]);
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/forgot');
        exit();
    }
});
// ===========================================================================================

?>