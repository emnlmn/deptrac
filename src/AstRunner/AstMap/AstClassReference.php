<?php

declare(strict_types=1);

namespace SensioLabs\Deptrac\AstRunner\AstMap;

class AstClassReference
{
    /** @var ClassLikeName */
    private $classLikeName;

    /** @var AstFileReference|null */
    private $fileReference;

    /** @var AstDependency[] */
    private $dependencies;

    /** @var AstInherit[] */
    private $inherits;

    /**
     * @param AstInherit[]    $inherits
     * @param AstDependency[] $dependencies
     */
    public function __construct(ClassLikeName $classLikeName, array $inherits = [], array $dependencies = [])
    {
        $this->classLikeName = $classLikeName;
        $this->dependencies = $dependencies;
        $this->inherits = $inherits;
    }

    public function withFileReference(AstFileReference $astFileReference): self
    {
        $instance = clone $this;
        $instance->fileReference = $astFileReference;

        return $instance;
    }

    public function getFileReference(): ?AstFileReference
    {
        return $this->fileReference;
    }

    public function getClassLikeName(): ClassLikeName
    {
        return $this->classLikeName;
    }

    /**
     * @return AstDependency[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @return AstInherit[]
     */
    public function getInherits(): array
    {
        return $this->inherits;
    }
}
