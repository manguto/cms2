<?php

use manguto\cms\ldb\Database;
use manguto\cms\gms\PageAdmin;

// ---------------------------------------------- database
$app->get('/admin/database', function () {
    // User::verifyLogin(); //ATIVAR
    
    $page = new PageAdmin();
    $page->setTpl("database", [
        'Database_Manager' => Database::Manager(),
        'POST' => ''
    ]);
});

// ---------------------------------------------- database table
$app->get('/admin/database/:tablename', function ($tablename) {
    // User::verifyLogin(); //ATIVAR
    
    $page = new PageAdmin();
    $page->setTpl("database", [
        'Database_Manager' => Database::Manager($tablename),
        'POST' => ''
    ]);
});


// ---------------------------------------------- database table (action) 
$app->post('/admin/database/:tablename', function ($tablename) {
    // User::verifyLogin(); //ATIVAR
    
    $page = new PageAdmin();
    $page->setTpl("database", [
        'Database_Manager' => Database::Manager($tablename),
        'POST' => ''//Arrays::arrayShow($_POST)
    ]);
});
    
    