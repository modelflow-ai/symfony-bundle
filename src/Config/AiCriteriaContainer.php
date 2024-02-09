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

use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;

final readonly class AiCriteriaContainer implements AiCriteriaInterface, \Stringable
{
    public function __construct(
        private AiCriteriaInterface $inner,
    ) {
    }

    public function matches(AiCriteriaInterface $toMatch): bool
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
