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

use Symfony\Component\Serializer\Annotation\Context;

final class Article
{
    public string $title;
    public string $slug;
    public string $body;

    #[Context(['datetime_format' => 'Y-m-d H:i:s'])]
    public \DateTimeInterface $publishedAt;
}
