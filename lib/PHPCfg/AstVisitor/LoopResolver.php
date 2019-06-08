<?php

/*
 * This file is part of PHP-CFG, a Control flow graph implementation for PHP
 *
 * @copyright 2015 Anthony Ferrara. All rights reserved
 * @license MIT See LICENSE at the root of the project for more info
 */

namespace PHPCfg\AstVisitor;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\Label;
use PhpParser\NodeVisitorAbstract;

// 循环分析，搞了两个栈用于存储，一个是continueStack和breakStack，用于确定continue和break能够跳到哪个位置
// 发现有循环，则加个lable，
class LoopResolver extends NodeVisitorAbstract {
    
    protected static $labelCounter = 0;
    protected $continueStack = [];
    protected $breakStack = [];

    public function enterNode(Node $node) {
        switch ($node->getType()) {
            case 'Stmt_Break':
                return $this->resolveStack($node, $this->breakStack);
            case 'Stmt_Continue':
                return $this->resolveStack($node, $this->continueStack);
            case 'Stmt_Switch':
                $lbl = $this->makeLabel();
                $this->breakStack[] = $lbl;
                $this->continueStack[] = $lbl;
                break;
            case 'Stmt_Do':
            case 'Stmt_While':
            case 'Stmt_For':
            case 'Stmt_Foreach':
            // 进入node时压栈
                $this->continueStack[] = $this->makeLabel();
                $this->breakStack[] = $this->makeLabel();
                break;
        }
        return null;
    }

    public function leaveNode(Node $node) {
        switch ($node->getType()) {
            case 'Stmt_Do':
            case 'Stmt_While':
            case 'Stmt_For':
            case 'Stmt_Foreach':
            // 退出时弹栈
                $node->stmts[] = new Label(array_pop($this->continueStack));
                return [$node, new Label(array_pop($this->breakStack))];
            case 'Stmt_Switch':
                // php的continue是个有点微妙的东西，后面可以加数字，continue 1跳一层循环，在switch中相当于break，其他的数字是跳到某层循环，继续下一个
                array_pop($this->continueStack);
                return [$node, new Label(array_pop($this->breakStack))];
        }
    }

    protected function resolveStack(Node $node, array $stack) {
        if (!$node->num) {
            return new Goto_(end($stack), $node->getAttributes());
        }
        if ($node->num instanceof LNumber) {
            $num = $node->num->value - 1;
            if ($num >= count($stack)) {
                throw new \LogicException("Too high of a count for " . $node->getType());
            }
            $loc = array_slice($stack, -1 * $num, 1);
            return new Goto_($loc[0], $node->getAttributes());
        }

        throw new \LogicException("Unimplemented Node Value Type");
    }

    protected function makeLabel() {
        return 'compiled_label_' . mt_rand(0, mt_getrandmax()) . '_' . self::$labelCounter++;
    }

}
