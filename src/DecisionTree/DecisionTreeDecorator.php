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

use ModelflowAi\DecisionTree\Behaviour\CriteriaBehaviour;
use ModelflowAi\DecisionTree\Behaviour\SupportsBehaviour;
use ModelflowAi\DecisionTree\DecisionRuleInterface;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;

/**
 * @template T of CriteriaBehaviour
 * @template U of SupportsBehaviour
 *
 * @implements DecisionTreeInterface<T, U>
 */
final readonly class DecisionTreeDecorator implements DecisionTreeInterface
{
    /**
     * @var DecisionTreeInterface<T, U>
     */
    private DecisionTreeInterface $inner;

    /**
     * @param \Traversable<DecisionRuleInterface<T, U>> $rules
     */
    public function __construct(
        \Traversable $rules,
    ) {
        $this->inner = new DecisionTree(\iterator_to_array($rules));
    }

    public function determineAdapter(object $request): object
    {
        return $this->inner->determineAdapter($request);
    }
}
