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

use Sigwin\YASSG\Linkable;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Validator\Constraints as Assert;

final class Article implements Linkable
{
    #[Assert\NotBlank]
    public string $title;
    #[Assert\NotBlank]
    public string $slug;
    #[Assert\NotBlank]
    public string $body;
    public ?string $image = null;
    public ?self $previous = null;

    #[Assert\NotBlank]
    #[Context(['datetime_format' => 'Y-m-d H:i:s'])]
    public \DateTimeInterface $publishedAt;

    public function getImage(): ?string
    {
        return $this->image;
    }

    #[\Override]
    public function getLinkRouteName(): string
    {
        return 'article';
    }

    #[\Override]
    public function getLinkRouteParameters(): array
    {
        return [
            'slug' => $this->slug,
        ];
    }
}
