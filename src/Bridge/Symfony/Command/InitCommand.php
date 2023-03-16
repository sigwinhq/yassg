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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final class InitCommand extends Command
{
    private const SOURCE_BASIC = 'basic';
    private const SOURCE_DEMO = 'demo';
    // private const SOURCE_GITHUB = 'github';
    private const SOURCE_GITLAB = 'gitlab';

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
            ->setDescription('Init a new project')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Namespace to setup the customization support for', 'App')
            ->addOption(self::SOURCE_DEMO, null, InputOption::VALUE_NONE, 'Generate the demo site showcasing most common use cases')
            // ->addOption(self::SOURCE_GITHUB, null, InputOption::VALUE_NONE, 'Generate Github Actions / Github Pages support')
            ->addOption(self::SOURCE_GITLAB, null, InputOption::VALUE_NONE, 'Generate Gitlab CI / Gitlab Pages support')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Sigwin YASSG init');

        umask(0022);
        $filesystem = new Filesystem();

        /**
         * @var string $namespace
         */
        $namespace = $input->getOption('namespace');
        $namespace = trim($namespace, '\\');

        $style->section(sprintf('Namespace: %1$s', $namespace));

        $style->writeln(sprintf('Ensuring folder <comment>%1$s</comment>', $this->baseDir.'/src/'));
        $filesystem->mkdir($this->baseDir.'/src/');
        $composerFile = $this->baseDir.'/composer.json';
        $style->writeln(sprintf('Registering namespace <comment>%1$s</comment> in <info>%2$s</info>', $namespace, $composerFile));
        $composer = [];
        if (file_exists($composerFile)) {
            /** @var string $composer */
            $composer = file_get_contents($composerFile);

            /** @var array $composer */
            $composer = json_decode($composer, true, 512, \JSON_THROW_ON_ERROR);
        }
        $composer = array_replace($composer, [
            'autoload' => [
                'psr-4' => [
                    $namespace.'\\' => 'src',
                ],
            ],
        ]);
        file_put_contents($composerFile, json_encode($composer, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));

        $style->writeln(sprintf('Ensuring folder <comment>%1$s</comment>', $this->baseDir.'/config/'));
        $filesystem->mkdir($this->baseDir.'/config/');
        $servicesFile = $this->baseDir.'/config/services.yaml';
        $style->writeln(sprintf('Registering namespace <comment>%1$s</comment> in <info>%2$s</info>', $namespace, $servicesFile));
        /** @var array $services */
        $services = file_exists($servicesFile) ? Yaml::parseFile($servicesFile) : [];
        $services = array_replace($services, [
            'services' => [
                '_defaults' => [
                    'autoconfigure' => true,
                    'autowire' => true,
                ],
                $namespace.'\\' => [
                    'resource' => '../src',
                ],
            ],
        ]);
        file_put_contents($servicesFile, Yaml::dump($services, 4));

        $sourceDirs = [
            self::SOURCE_BASIC => false,
            self::SOURCE_DEMO => true,
            // self::SOURCE_GITHUB => true,
            self::SOURCE_GITLAB => true,
        ];
        foreach ($sourceDirs as $sourceDir => $optional) {
            if ($optional === true && $input->getOption($sourceDir) === false) {
                continue;
            }

            $style->section(sprintf('Init: %1$s', ucfirst($sourceDir)));

            $finder = new Finder();
            $finder
                ->ignoreDotFiles(false)
                ->depth('== 0')
                ->in($this->initDir.'/'.$sourceDir)
            ;

            foreach ($finder as $file) {
                /** @var string $source */
                $source = $file->getRealPath();
                $target = $this->baseDir.'/'.$file->getFilename();

                if ($file->isFile()) {
                    $filesystem->copy($source, $target, true);
                } else {
                    $filesystem->mirror($source, $target, null, ['override' => true]);
                }

                $style->writeln($target);
            }
        }

        return 0;
    }
}
