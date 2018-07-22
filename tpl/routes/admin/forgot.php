<?php
use manguto\cms\gms\PageAdmin;
use lib\model\User;
use manguto\cms\lib\ProcessResult;

// ===========================================================================================
$app->get('/admin/forgot', function () {
    
    $page = new PageAdmin();
    $page->setTpl("/forgot", [
        'form_action' => '/admin/forgot'
    ]);
});
// -------------------------------------------------------------------------
$app->post("/admin/forgot", function () {
    // deb($_POST);
    try {
        $user = User::getForgot(trim($_POST['email']));
        headerLocation('/admin/forgot/sent');
        exit();
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/admin/forgot');
        exit();
    }
});
// ===========================================================================================
$app->get('/admin/forgot/sent', function () {
    
    $page = new PageAdmin();
    
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

$app->get('/admin/forgot/reset', function () {
    
    $code = $_GET['code'];
    
    try {
        $user = User::validForgotDecrypt($code);
        $page = new PageAdmin();
        $page->setTpl("forgot-reset", [
            'form_action' => '/admin/forgot/reset',
            'name' => $user->getname(),
            'code' => $code
        ]);
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/admin/forgot/reset');
        exit();
    }
});
// -------------------------------------------------------------------------
$app->post('/admin/forgot/reset', function () {
    
    $code = $_POST['code'];
    
    try {
        $user = User::validForgotDecrypt($code);
        User::setForgotUsed($user->getrecoveryid());
        $user->setPassword(User::password_crypt($_POST['password']));
        $user->save();
        $page = new PageAdmin();
        $page->setTpl("forgot-reset-success",[
            'link_form_login'=>'/admin/login'
        ]);
    } catch (Exception $e) {
        ProcessResult::setError($e->getMessage());
        headerLocation('/admin/forgot');
        exit();
    }
});
// -------------------------------------------------------------------------

?>