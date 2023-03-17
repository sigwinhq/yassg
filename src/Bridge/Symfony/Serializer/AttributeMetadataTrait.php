<?php

declare(strict_types=1);

/*
 * This file is part of the Sigwin Yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

/**
 * @internal
 */
trait AttributeMetadataTrait
{
    private ClassMetadataFactoryInterface $classMetadataFactory;

    private function getProperties(string $class): array
    {
        $classMetadata = $this->classMetadataFactory->getMetadataFor($class);

        $properties = [];
        foreach ($classMetadata->getAttributesMetadata() as $attributeMetadata) {
            $properties[] = $attributeMetadata->getName();
        }

        return $properties;
    }
}
