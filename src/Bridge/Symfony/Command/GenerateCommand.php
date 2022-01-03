<?php

namespace Sigwin\YASSG\Bridge\Symfony\Command;

use Sigwin\YASSG\Bridge\Symfony\Routing\Generator\FilenameUrlGenerator;
use Sigwin\YASSG\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GenerateCommand extends Command
{
    protected static $defaultName = 'yassg:generate';
    
    private Generator $generator;
    
    public function __construct(Generator $generator)
    {
        parent::__construct(self::$defaultName);
        
        $this->generator = $generator;
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
        
        $this->generator->generate($input->getArgument('url'), function (Request $request, Response $response, string $path) use ($style): void {
            $style->writeln($request->getUri());
        });
        
        return 0;
    }
}