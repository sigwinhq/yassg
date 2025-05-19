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

use Sigwin\YASSG\DatabaseProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(name: 'yassg:validate', description: 'Validate the databases')]
final class ValidateCommand extends Command
{
    /**
     * @param array<string> $databases
     */
    public function __construct(private array $databases, private DatabaseProvider $databaseProvider, private ValidatorInterface $validator)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('database', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Database name')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Sigwin YASSG validate');

        /**
         * @var array<string> $databases
         */
        $databases = $input->getArgument('database');
        if ($databases === []) {
            $databases = $this->databases;
        }

        foreach ($databases as $name) {
            $style->section($name);
            $database = $this->databaseProvider->getDatabase($name);
            foreach ($database->findAll() as $id => $resource) {
                $style->write($id);
                $violations = $this->validator->validate($resource);
                if ($violations->count() > 0) {
                    $style->writeln(' <error>Error</error>');
                    foreach ($violations as $violation) {
                        $style->error(\sprintf('%1$s: %2$s', $violation->getPropertyPath(), $violation->getMessage()));
                    }
                } else {
                    $style->writeln(' <info>OK</info>');
                }
            }
        }

        return Command::SUCCESS;
    }
}
