<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Struct;

class VoucherCodeGeneratorConfig
{
    public const DEFAULT_VALID_LETTERS = 'abcdefghijklmnopqrstuvwxyz';

    public const DEFAULT_VALID_NUMBERS = '0123456789';

    public const DEFAULT_THRESHOLD     = 0.1;

    public const DEFAULT_PATTERN       = '%s%s%s%s-%s%s%s%s-%d%d';

    public const DEFAULT_MAX_LOOPS     = 10000;

    /**
     * @var VoucherCollection
     */
    private $reservedVouchers;

    /**
     * @var BadwordCollection
     */
    private $badwords;

    /**
     * @var string
     */
    private $validLetters      = self::DEFAULT_VALID_LETTERS;

    /**
     * @var string
     */
    private $validNumbers      = self::DEFAULT_VALID_NUMBERS;

    /**
     * @var float
     */
    private $threshold         = self::DEFAULT_THRESHOLD;

    /**
     * @var string
     */
    private $pattern           = self::DEFAULT_PATTERN;

    /**
     * @var int
     */
    private $maxLoops          = self::DEFAULT_MAX_LOOPS;

    /**
     * @var int
     */
    private $numOfVoucherCodes = 1;

    public function __construct()
    {
        $this->badwords         = new BadwordCollection();
        $this->reservedVouchers = new VoucherCollection();
    }

    public function getValidLetters(): string
    {
        return $this->validLetters;
    }

    public function setValidLetters(string $validLetters): self
    {
        $this->validLetters = $validLetters;

        return $this;
    }

    public function getValidNumbers(): string
    {
        return $this->validNumbers;
    }

    public function setValidNumbers(string $validNumbers): self
    {
        $this->validNumbers = $validNumbers;

        return $this;
    }

    public function getThreshold(): float
    {
        return $this->threshold;
    }

    public function setThreshold(float $threshold): self
    {
        $this->threshold = $threshold;

        return $this;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getMaxLoops(): int
    {
        return $this->maxLoops;
    }

    public function setMaxLoops(int $maxLoops): self
    {
        $this->maxLoops = $maxLoops;

        return $this;
    }

    public function getReservedVouchers(): VoucherCollection
    {
        return $this->reservedVouchers;
    }

    public function setReservedVouchers(VoucherCollection $reservedVouchers): self
    {
        $this->reservedVouchers = $reservedVouchers;

        return $this;
    }

    public function getBadwords(): BadwordCollection
    {
        return $this->badwords;
    }

    public function setBadwords(BadwordCollection $badwords): self
    {
        $this->badwords = $badwords;

        return $this;
    }

    public function getNumOfVoucherCodes(): int
    {
        return $this->numOfVoucherCodes;
    }

    public function setNumOfVoucherCodes(int $numOfVoucherCodes): self
    {
        $this->numOfVoucherCodes = $numOfVoucherCodes;

        return $this;
    }
}
