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

final class Product
{
    #[Localized]
    public string $name;
    #[Localized]
    public string $slug;
    public int $index;
    public ?string $file = null;

    /**
     * @var Collection<string, Category>
     */
    public Collection $categories;

    public function getName(): string
    {
        if ($this->name === 'Please throw an RuntimeException') {
            throw new \LogicException('Product not properly overridden');
        }

        return $this->name;
    }

    public function getCategory(): ?Category
    {
        /**
         * @psalm-suppress InvalidArgument
         */
        $category = current($this->categories);

        return $category !== false ? $category : null;
    }
}
