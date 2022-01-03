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

namespace Sigwin\YASSG\Bridge\Symfony\Command;

use Sigwin\YASSG\Bridge\Symfony\Routing\BuildRequestContextFactory;
use Sigwin\YASSG\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GenerateCommand extends Command
{
    protected static $defaultName = 'yassg:generate';

    private Generator $generator;
    private BuildRequestContextFactory $contextFactory;

    public function __construct(Generator $generator, BuildRequestContextFactory $contextFactory)
    {
        parent::__construct(self::$defaultName);

        $this->generator = $generator;
        $this->contextFactory = $contextFactory;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Generate the site')
            ->addArgument('url', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Sigwin YASSG');

        $buildUrl = $input->getArgument('url');
        $this->contextFactory->setBuildUrl($buildUrl);

        $this->generator->generate($buildUrl, static function (Request $request, Response $response, string $path) use ($style, $buildUrl): void {
            $style->writeln(str_replace('http://localhost', $buildUrl, $request->getUri()));
        });

        return 0;
    }
}
