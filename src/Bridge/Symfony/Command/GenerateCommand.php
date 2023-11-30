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

namespace Sigwin\YASSG\Bridge\Symfony\Command;

use Sigwin\YASSG\Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

#[AsCommand(
    name: 'yassg:generate'
)]
final class GenerateCommand extends Command
{
    private Generator $generator;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(Generator $generator, UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct();

        $this->generator = $generator;
        $this->urlGenerator = $urlGenerator;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate the site')
            ->addArgument('url', InputArgument::REQUIRED, 'Base URL to generate for')
            ->addOption('index-file', null, InputOption::VALUE_NONE, 'Add the index.html to generated routes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Sigwin YASSG');

        /**
         * @phpstan-var string $buildUrl
         *
         * @psalm-suppress UnnecessaryVarAnnotation Psalm's Symfony plugin solves this, but not PHPStan's
         */
        $buildUrl = $input->getArgument('url');
        $buildUrl = rtrim($buildUrl, '/');

        /**
         * @phpstan-var bool $indexFile
         *
         * @psalm-suppress UnnecessaryVarAnnotation Psalm's Symfony plugin solves this, but not PHPStan's
         */
        $indexFile = $input->getOption('index-file');

        $context = RequestContext::fromUri($buildUrl);
        $context->setParameter('index-file', $indexFile);
        $this->urlGenerator->setContext($context);

        $this->generator->generate(static function (Request $request, Response $response, string $path) use ($style): void {
            $style->writeln($request->getUri());

            if ($style->isDebug()) {
                $style->info(sprintf('Response code: %1d$', $response->getStatusCode()));
                $style->info(sprintf('Written to: %1s$', $path));
            }
        });

        return 0;
    }
}
