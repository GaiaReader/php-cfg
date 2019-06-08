<?php

use PhpParser\ParserFactory;

require __DIR__ . "/vendor/autoload.php";

$graphviz = true;
list($fileName, $code) = getCode($argc, $argv);

$parser = new PHPCfg\Parser((new ParserFactory)->create(ParserFactory::PREFER_PHP7));

$declarations = new PHPCfg\Visitor\DeclarationFinder;
$calls = new PHPCfg\Visitor\CallFinder;
$variables = new PHPCfg\Visitor\VariableFinder;

$traverser = new PHPCfg\Traverser;

// 这几个visitor是针对cfg的，要进一步分析如何应用cfg，需要认真分析这几个visitor
$traverser->addVisitor($declarations);
$traverser->addVisitor($calls);
$traverser->addVisitor(new PHPCfg\Visitor\Simplifier);
$traverser->addVisitor($variables);

$script = $parser->parse($code, __FILE__);
$traverser->traverse($script);

if ($graphviz) {
    $dumper = new PHPCfg\Printer\GraphViz();
    echo $dumper->printScript($script);
} else {
    $dumper = new PHPCfg\Printer\Text();
    echo $dumper->printScript($script);
}

function getCode($argc, $argv) {
    if ($argc >= 2) {
        if (strpos($argv[1], '<?php') === 0) {
            return ['command line code', $argv[1]];
        } else {
            return [$argv[1], file_get_contents($argv[1])];
        }
    } else {
        return [__FILE__, <<<'EOF'
<?php
function foo(array $a) {
    $a[] = 1;
}
EOF
        ];
    }
}
/*
Block#1
    Stmt_Function<foo>
    Terminal_Return
        expr: LITERAL(1)

Function foo():
Block#1
    Expr_Param
        name: LITERAL('a')
        result: Var#1<$a>
    Expr_ArrayDimFetch
        var: Var#1<$a>
        result: Var#2
    Expr_Assign
        var: Var#2
        expr: LITERAL(1)
        result: Var#3
    Terminal_Return
        expr: LITERAL(NULL)
可以看出 $a[]=1;
分解为
$tmp = $a[];
$tmp = 1;

*/
