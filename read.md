# 解决了什么问题？

AST转CFG

## 怎么做的？

AST转CFG常见的方法有：

- AST转中间表示，如SSA，然后再转CFG

- 遍历AST，构建基本块，直接得到CFG

简单看下demo和test，可以知道是使用了SSA，即静态单赋值

SSA的构造算法的来源为[Simple and Efficient Construction of Static Single Assignment Form](https://pp.info.uni-karlsruhe.de/uploads/publikationen/braun13cc.pdf)，这篇论文提出的算法为将AST/bytecode转SSA。



先看下`PHPCfg/Parser`







```php
PHPCfg\Visitor\DeclarationFinder
PHPCfg\Visitor\CallFinder
PHPCfg\Visitor\VariableFinder
```

