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

namespace ModelflowAi\Integration\Symfony\DecisionTree;

use ModelflowAi\Core\DecisionTree\AIModelDecisionTree;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface;
use ModelflowAi\Core\DecisionTree\DecisionRuleInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIRequestInterface;

final readonly class AIModelDecisionTreeDecorator implements AIModelDecisionTreeInterface
{
    private AIModelDecisionTreeInterface $inner;

    /**
     * @param \Traversable<DecisionRuleInterface> $rules
     */
    public function __construct(
        \Traversable $rules,
    ) {
        $this->inner = new AIModelDecisionTree(\iterator_to_array($rules));
    }

    public function determineAdapter(AIRequestInterface $request): AIModelAdapterInterface
    {
        return $this->inner->determineAdapter($request);
    }
}
