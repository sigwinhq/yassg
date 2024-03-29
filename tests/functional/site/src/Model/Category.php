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

namespace Sigwin\YASSG\Test\Functional\Site\Model;

use Sigwin\YASSG\Bridge\Attribute\Localized;
use Sigwin\YASSG\Collection;

final class Category
{
    public string $slug;
    #[Localized]
    public string $name;
    #[Localized]
    public ?string $description = null;

    public ?self $parent = null;

    /** @var Collection<string, Product> */
    public Collection $products;

    public function method(): string
    {
        return __METHOD__;
    }

    public function getDescription(): ?string
    {
        if ($this->description === 'Please throw an RuntimeException') {
            throw new \LogicException('Category not properly overridden');
        }

        return $this->description;
    }
}
