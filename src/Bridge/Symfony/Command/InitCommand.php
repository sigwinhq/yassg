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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class InitCommand extends Command
{
    protected static $defaultName = 'yassg:init';

    private string $initDir;
    private string $baseDir;

    public function __construct(string $initDir, string $baseDir)
    {
        $this->baseDir = $baseDir;
        $this->initDir = $initDir;

        parent::__construct('yassg:init');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Init a new project');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Sigwin YASSG init');

        $finder = new Finder();
        $finder
            ->ignoreDotFiles(false)
            ->depth('== 0')
            ->in($this->initDir);

        $filesystem = new Filesystem();
        foreach ($finder as $file) {
            /** @var string $source */
            $source = $file->getRealPath();
            $target = $this->baseDir.'/'.$file->getFilename();

            if ($file->isFile()) {
                $filesystem->copy($source, $target);
            } else {
                $filesystem->mirror($source, $target);
            }

            $style->writeln($target);
        }

        return 0;
    }
}
