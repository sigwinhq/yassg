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

namespace Sigwin\YASSG\Bridge\PrestaSitemap;

use Presta\SitemapBundle\Sitemap\Url\Url;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

final class Urlset extends \Presta\SitemapBundle\Sitemap\Urlset
{
    public function __construct(string $loc)
    {
        parent::__construct($loc);

        $this->lastmod = new \DateTimeImmutable('1970-01-01 00:00:00');
    }

    public function addUrl(Url $url): void
    {
        parent::addUrl($url);

        if ($url instanceof UrlConcrete) {
            $this->lastmod = max($url->getLastmod() ?? new \DateTimeImmutable(), $this->lastmod);
        }
    }
}
