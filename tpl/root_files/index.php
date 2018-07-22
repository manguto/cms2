<?php
require_once ("vendor/autoload.php");
require_once ("Configurations.php");
require_once ("Parameters.php");
require_once ("Functions.php");

use Slim\Slim;
use manguto\cms\lib\Diretorios;

// SLIM FRAMEWORK CONTROL
{
    $app = new Slim();
    
    $app->config('debug', true);
    
    // ROTAS FRONT-END (SITE)
    {
        $arquivos = Diretorios::obterArquivosPastas('routes/site', true, true, false);
        foreach ($arquivos as $arquivo) {
            require_once $arquivo;
        }
    }
    
    // ROTAS BACK-END (ADMIN)
    {
        $arquivos = Diretorios::obterArquivosPastas('routes/admin', true, true, false);
        foreach ($arquivos as $arquivo) {
            require_once $arquivo;
        }
    }
    
    $app->run();
}

?>