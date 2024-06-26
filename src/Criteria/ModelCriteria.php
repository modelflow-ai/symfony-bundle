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

namespace ModelflowAi\Integration\Symfony\Criteria;

use ModelflowAi\DecisionTree\Criteria\CriteriaInterface;
use ModelflowAi\DecisionTree\Criteria\FlagCriteriaTrait;

enum ModelCriteria: string implements CriteriaInterface
{
    use FlagCriteriaTrait;

    // Ollama
    case LLAMA2 = 'llama2';
    case NEXUSRAVEN = 'nexusraven';
    case LLAVA = 'llava';

    // OpenAI
    case GPT4 = 'gpt4';
    case GPT3_5 = 'gpt3.5-turbo';
    case DALL_E_3 = 'dall-e-3';
    case DALL_E_2 = 'dall-e-2';

    // Mistral
    case MISTRAL_TINY = 'mistral_tiny';
    case MISTRAL_SMALL = 'mistral_small';
    case MISTRAL_MEDIUM = 'mistral_medium';
    case MISTRAL_LARGE = 'mistral_large';

    // Anthropic
    case CLAUDE_3_OPUS = 'claude-3-opus-20240229';
    case CLAUDE_3_5_SONNET = 'claude-3-5-sonnet-20240620';
    case CLAUDE_3_SONNET = 'claude-3-sonnet-20240229';
    case CLAUDE_3_HAIKU = 'claude-3-haiku-20240307';
}
