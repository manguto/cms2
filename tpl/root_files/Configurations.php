<?php

// ##################################################################################################
// ################################ SISTEM CONFIGURATIONS ###########################################
// ##################################################################################################

// nome do sistema
define("SIS_NAME", "Setor de Transportes");

// abrev. do sistema
define("SIS_ABREV", "STR");

// abrev. do sistema
define("SIS_FOLDERNAME", "transportes");

// abrev. do sistema
define("SIS_EMAIL", "marcos.torres@ufrpe.br");

// url real do sistema
define("SIS_URL", "http://localhost/".SIS_FOLDERNAME);


// ##################################################################################################
// ############################################## SISTEM  ###########################################
// ##################################################################################################

//locale
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

//virtual hosts are active?
define('VIRTUAL_HOST_ACTIVE',false);

// Gmail settings
define("GMAIL_USERNAME", "mangutorres@gmail.com");
define("GMAIL_PASSWORD", "!QAZXSW@");



?>