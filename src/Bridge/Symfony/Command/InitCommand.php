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
use Symfony\Component\Finder\SplFileInfo;

final class InitCommand extends Command
{
    protected static $defaultName = 'yassg:init';

    public function __construct(private string $initDir, private string $baseDir)
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Init a new project');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Sigwin YASSG init');

        $finder = new \Symfony\Component\Finder\Finder();
        $finder
            ->ignoreDotFiles(false)
            ->depth('== 0')
            ->in($this->initDir);

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
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
