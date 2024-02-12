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

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Request\Criteria\PrivacyCriteria;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\PromptTemplate\ChatPromptTemplate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ChatCommand extends Command
{
    public function __construct(
        private readonly AIRequestHandlerInterface $requestHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var AIChatResponse $response */
        $response = $this->requestHandler->createChatRequest(...ChatPromptTemplate::create(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'You are an {feeling} bot'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello {where}!'),
        )->format(['where' => 'world', 'feeling' => 'angry']))
            ->addCriteria(PrivacyCriteria::HIGH)
            ->build()
            ->execute();

        $output->writeln($response->getMessage()->content);

        return 0;
    }
}
