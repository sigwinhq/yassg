<?php

namespace App\Model;

use Sigwin\YASSG\Linkable;

final class Page implements Linkable
{
    public string $slug;
    public string $title;
    public string $image;

    #[\Override]
    public function getLinkRouteName(): string
    {
        return 'demo';
    }

    #[\Override]
    public function getLinkRouteParameters(): array
    {
        return [];
    }
}
