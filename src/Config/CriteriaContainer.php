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

namespace ModelflowAi\Integration\Symfony\Config;

use ModelflowAi\DecisionTree\Criteria\CriteriaInterface;
use ModelflowAi\DecisionTree\DecisionEnum;

final readonly class CriteriaContainer implements CriteriaInterface, \Stringable
{
    public function __construct(
        private CriteriaInterface $inner,
    ) {
    }

    public function matches(CriteriaInterface $toMatch): DecisionEnum
    {
        return $this->inner->matches($toMatch);
    }

    public function getValue(): int|string
    {
        return $this->inner->getValue();
    }

    public function getName(): string
    {
        return $this->inner->getName();
    }

    public function __toString(): string
    {
        return \sprintf(
            '!php/const %s::%s',
            $this->inner::class,
            $this->inner->getName(),
        );
    }
}
