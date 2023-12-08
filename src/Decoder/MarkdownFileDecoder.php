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

namespace Sigwin\YASSG\Decoder;

use League\CommonMark\ConverterInterface;
use League\CommonMark\Extension\FrontMatter\FrontMatterProviderInterface;
use Sigwin\YASSG\FileDecoder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

final readonly class MarkdownFileDecoder implements FileDecoder
{
    use FileDecoderTrait;

    private const EXTENSIONS = ['md', 'markdown'];

    public function __construct(private ConverterInterface $converter, private Environment $twig) {}

    public function decode(\SplFileInfo $file): array
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new \RuntimeException('Invalid file path');
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException('Failed to read file');
        }

        $metadata = [];
        if (str_contains($content, '{{') || str_contains($content, '{%')) {
            if (str_starts_with($content, '---')) {
                $end = mb_strpos($content, '---', 3);
                if ($end === false) {
                    throw new \RuntimeException('Invalid frontmatter, missing closing ---');
                }
                $frontMatter = mb_substr($content, 3, $end - 3);

                try {
                    /** @var array<string, string> $metadata */
                    $metadata = Yaml::parse($frontMatter);
                } catch (ParseException $e) {
                    throw new \RuntimeException('Invalid frontmatter, failed to parse YAML: '.$e->getMessage(), 0, $e);
                }
            }

            $content = $this->twig->createTemplate($content)->render([
                'item' => $metadata,
                '_path' => $file->getPathname(),
            ]);
        }

        $result = $this->converter->convert($content);
        if ($result instanceof FrontMatterProviderInterface) {
            /** @var array<string, string> $metadata */
            $metadata = $result->getFrontMatter();
        }
        $metadata['body'] = $result->getContent();

        return $metadata;
    }
}
