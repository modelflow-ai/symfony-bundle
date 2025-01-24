<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ModelflowAi\Integration\Symfony\Command;

use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ChatCommand extends Command
{
    public function __construct(
        private readonly AIChatRequestHandlerInterface $chatRequestHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $messages = [];

        while (true) {
            /** @var string $question */
            $question = $io->ask('You (close with "exit"): ');
            if ('exit' === $question) {
                break;
            }

            $response = $this->chatRequestHandler
                ->createStreamedRequest(...$messages)
                ->addUserMessage($question)
                ->execute();

            foreach ($response->getMessageStream() as $message) {
                $io->write($message->content);
            }

            $io->newLine(2);

            $messages = $response->getRequest()->getMessages();
            $messages[] = new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, $response->getMessage()->content);
        }

        return Command::SUCCESS;
    }
}
