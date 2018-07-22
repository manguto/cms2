<?php

use manguto\cms\gms\Page;

// ---------------------------------------------- formulario

$app->get('/', function () {
        
    $show = '';
    $page = new Page();
    $page->setTpl("index", [
        'show' => $show
    ]);
});



    