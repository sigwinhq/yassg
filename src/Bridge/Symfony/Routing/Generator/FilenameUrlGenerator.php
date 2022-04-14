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

namespace Sigwin\YASSG\Bridge\Symfony\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

final class FilenameUrlGenerator implements UrlGeneratorInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private array $stripParameters, private array $routes)
    {
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $this->stripParameters($this->stripParameters[$name] ?? [], $parameters);

        /** @var bool $indexFile */
        $indexFile = $this->getContext()->getParameter('index-file') ?? false;
        if (mb_strpos($this->routes[$name]['path'], '.') !== false) {
            $parameters['_filename'] = null;
        } elseif ($indexFile === true) {
            $parameters += ['_filename' => 'index.html'];
        }

        $url = $this->urlGenerator->generate($name, $parameters, $referenceType);
        if (parse_url($url, \PHP_URL_QUERY) !== null) {
            throw new \LogicException(sprintf('Query string found while generating route "%1$s", query strings are forbidden: %2$s', $name, $url));
        }

        // add a trailing slash if no file is in the URL
        $url .= (str_contains(basename($url), '.') === false && str_ends_with($url, '/') === false) ? '/' : '';

        return $url;
    }

    public function setContext(RequestContext $context): void
    {
        $this->urlGenerator->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->urlGenerator->getContext();
    }

    private function stripParameters(array $names, array &$parameters): void
    {
        foreach ($names as $name) {
            if (isset($parameters[$name])) {
                unset($parameters[$name]);
            }
        }
    }
}
