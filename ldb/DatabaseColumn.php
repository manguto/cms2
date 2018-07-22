<?php
namespace manguto\cms\ldb;

class DatabaseColumn
{

    public $name;

    public $type;

    public $default;

    public function __construct(string $name, $type = 'string', $default = '')
    {
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
    }

}

?>