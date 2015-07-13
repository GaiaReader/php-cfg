<?php

namespace PHPCfg\Op\Stmt;

use PHPCfg\Op\Stmt;
use PhpCfg\Block;

class Trait_ extends ClassLike {

    public function __construct($name, Block $stmts, array $attributes = array()) {
        parent::__construct($name, $stmts, $attributes);
        $this->name = $name;
        $this->stmts = $stmts;
    }

}