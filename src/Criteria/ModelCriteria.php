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

use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;
use ModelflowAi\Core\Request\Criteria\FlagCriteriaTrait;

enum ModelCriteria: string implements AiCriteriaInterface
{
    use FlagCriteriaTrait;

    case LLAMA2 = 'llama2';
    case NEXUSRAVEN = 'nexusraven';
    case LLAVA = 'llava';
    case GPT4 = 'gpt4';
    case GPT3_5 = 'gpt3.5';
    case MISTRAL_TINY = 'mistral_tiny';
    case MISTRAL_SMALL = 'mistral_small';
    case MISTRAL_MEDIUM = 'mistral_medium';
}
