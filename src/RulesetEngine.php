<?php

declare(strict_types=1);

namespace SensioLabs\Deptrac;

use SensioLabs\Deptrac\Configuration\Configuration;
use SensioLabs\Deptrac\Dependency\Result;
use SensioLabs\Deptrac\RulesetEngine\Allowed;
use SensioLabs\Deptrac\RulesetEngine\Context;
use SensioLabs\Deptrac\RulesetEngine\SkippedViolation;
use SensioLabs\Deptrac\RulesetEngine\Uncovered;
use SensioLabs\Deptrac\RulesetEngine\Violation;

class RulesetEngine
{
    public function process(
        Result $dependencyResult,
        ClassNameLayerResolverInterface $classNameLayerResolver,
        Configuration $configuration
    ): Context {
        $rules = [];

        $configurationRuleset = $configuration->getRuleset();
        $configurationSkippedViolation = $configuration->getSkipViolations();

        foreach ($dependencyResult->getDependenciesAndInheritDependencies() as $dependency) {
            $layerNames = $classNameLayerResolver->getLayersByClassName($dependency->getClassLikeNameA());

            foreach ($layerNames as $layerName) {
                $allowedDependencies = $configurationRuleset->getAllowedDependencies($layerName);

                $layersNamesClassB = $classNameLayerResolver->getLayersByClassName($dependency->getClassLikeNameB());

                if (0 === count($layersNamesClassB)) {
                    $rules[] = new Uncovered($dependency, $layerName);
                    continue;
                }

                foreach ($layersNamesClassB as $layerNameOfDependency) {
                    if ($layerName === $layerNameOfDependency) {
                        continue;
                    }

                    if (in_array($layerNameOfDependency, $allowedDependencies, true)) {
                        $rules[] = new Allowed($dependency, $layerName, $layerNameOfDependency);
                        continue;
                    }

                    if ($configurationSkippedViolation->isViolationSkipped($dependency->getClassLikeNameA(), $dependency->getClassLikeNameB())) {
                        $rules[] = new SkippedViolation($dependency, $layerName, $layerNameOfDependency);
                        continue;
                    }

                    $rules[] = new Violation($dependency, $layerName, $layerNameOfDependency);
                }
            }
        }

        return new Context($rules);
    }
}
