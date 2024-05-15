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

use ModelflowAi\Core\Behaviour\CriteriaBehaviour;
use ModelflowAi\Core\Behaviour\SupportsBehaviour;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTree;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface;
use ModelflowAi\Core\DecisionTree\DecisionRuleInterface;

/**
 * @template T of CriteriaBehaviour
 * @template U of SupportsBehaviour
 *
 * @implements AIModelDecisionTreeInterface<T, U>
 */
final readonly class AIModelDecisionTreeDecorator implements AIModelDecisionTreeInterface
{
    /**
     * @var AIModelDecisionTreeInterface<T, U>
     */
    private AIModelDecisionTreeInterface $inner;

    /**
     * @param \Traversable<DecisionRuleInterface<T, U>> $rules
     */
    public function __construct(
        \Traversable $rules,
    ) {
        $this->inner = new AIModelDecisionTree(\iterator_to_array($rules));
    }

    public function determineAdapter(object $request): object
    {
        return $this->inner->determineAdapter($request);
    }
}
