<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Components;

class RuleBasedContainerFile
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var \Closure
     */
    private $condition;

    public function __construct(string $file, \Closure $condition)
    {
        $this->file      = $file;
        $this->condition = $condition;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getCondition(): \Closure
    {
        return $this->condition;
    }

    public function match(): bool
    {
        return (bool) $this->condition->call($this);
    }
}
