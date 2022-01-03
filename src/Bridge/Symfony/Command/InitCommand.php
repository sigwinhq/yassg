<?php

namespace Sigwin\YASSG\Bridge\Symfony\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\SplFileInfo;

class InitCommand extends Command
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
            ->in($this->initDir);
        
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $path = $this->baseDir .'/'. $file->getFilename();
            copy($file->getRealPath(), $path);
            
            $style->writeln($path);
        }

        return 0;
    }
}
