<?php

namespace manguto\cms\lib;



class Numeros
{
    static function QuantDigitos($numero,$digitos=2){
        return str_pad($numero, $digitos,'0',STR_PAD_LEFT);
    }
}

