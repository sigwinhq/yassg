<?php

declare(strict_types=1);

/*
 * This file is part of the yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG;

final class Database
{
    private DataSource $products;
    private DataSource $locale;

    public function __construct(DataSource $products, DataSource $locale)
    {
        $this->products = $products;
        $this->locale = $locale;
    }

    public function query(string $query, ?string $condition = null): array
    {
        dump(
            $this->products->count(),
            $this->locale->count()
        );

        $found = $this->products->find();

        dump($found);
        exit;

        exit(__METHOD__);
    }
}
