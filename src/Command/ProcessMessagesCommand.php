<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:process-messages',
    description: 'Process all messages using their handlers',
)]
final class ProcessMessagesCommand extends Command
{
    public function __construct(
        private readonly MessageService $service,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $messages = $this->service->findAll();

        foreach ($messages as $message) {
            if ($message->getProcessedAt() === null) {
                $this->service->process($message);
            }
        }

        $output->writeln('Messages processed.');

        return Command::SUCCESS;
    }
}
