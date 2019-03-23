<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;
use Rector\PhpSpecToPHPUnit\PhpSpecMockCollector;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;

final class AddMockPropertiesRector extends AbstractPhpSpecToPHPUnitRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var PhpSpecMockCollector
     */
    private $phpSpecMockCollector;

    public function __construct(ClassManipulator $classManipulator, PhpSpecMockCollector $phpSpecMockCollector)
    {
        $this->classManipulator = $classManipulator;
        $this->phpSpecMockCollector = $phpSpecMockCollector;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInPhpSpecBehavior($node)) {
            return null;
        }

        $classMocks = $this->phpSpecMockCollector->resolveClassMocksFromParam($node);

        foreach ($classMocks as $variable => $methods) {
            if (count($methods) <= 1) {
                continue;
            }

            $variableType = $this->phpSpecMockCollector->getTypeForClassAndVariable($node, $variable);

            $this->classManipulator->addPropertyToClass(
                $node,
                new VariableInfo($variable, $variableType . '|\PHPUnit\Framework\MockObject\MockObject')
            );
        }

        return null;
    }
}
