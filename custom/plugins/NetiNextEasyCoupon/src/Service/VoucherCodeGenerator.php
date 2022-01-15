<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Service\VoucherCodeGenerator\ValidationProcessor;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCodeGeneratorConfig;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCollection;

class VoucherCodeGenerator
{
    /**
     * @var string
     */
    public const NUMBER_PATTERN = 'd';

    /**
     * @var string
     */
    public const LETTER_PATTERN = 's';

    /**
     * @var VoucherCodeGeneratorConfig
     */
    private $config;

    /**
     * @var VoucherCollection
     */
    private $vouchers;

    /**
     * @var int
     */
    private $validLettersLength = 0;

    /**
     * @var int
     */
    private $validNumbersLength = 0;

    /**
     * @var false|string
     */
    private $badwordPattern     = false;

    /**
     * @var ValidationProcessor
     */
    private $validationProcessor;

    public function __construct(ValidationProcessor $validationProcessor)
    {
        $this->config              = new VoucherCodeGeneratorConfig();
        $this->vouchers            = new VoucherCollection();
        $this->validationProcessor = $validationProcessor;
    }

    public function __clone()
    {
        $this->vouchers->clear();
    }

    public function withConfig(VoucherCodeGeneratorConfig $config): self
    {
        $generator = clone $this;
        $generator->setConfig($config);

        return $generator;
    }

    /**
     * @return VoucherCollection
     * @throws \Exception
     */
    public function generateVoucherCodes(): VoucherCollection
    {
        $threshold                = $this->config->getThreshold();
        $pattern                  = $this->config->getPattern();
        $numOfVoucherCodes        = $this->config->getNumOfVoucherCodes();
        $maxLoops                 = $this->config->getMaxLoops();
        $voucherCodeCharPattern   = \sprintf('/(%%[%s%s])/', self::NUMBER_PATTERN, self::LETTER_PATTERN);
        $reservedVouchers         = $this->config->getReservedVouchers();
        $maxPossibleVouchersCodes = (
            $this->calculateMaxPossibleVoucherCodes() - $reservedVouchers->count()
        ) * $threshold;

        if ($numOfVoucherCodes > $maxPossibleVouchersCodes) {
            throw new \Exception(\sprintf(
                'The number of codes exceeds a value of %s%% of the possible generatable codes for the pattern "%s"',
                \round($threshold * 100, 2),
                $pattern
            ));
        }

        $that = $this;

        for ($i = 0; $i < $numOfVoucherCodes; $i++) {
            if (0 === $maxLoops) {
                throw new \Exception('Could not generate the defined number of codes.');
            }

            $voucherCode = \preg_replace_callback($voucherCodeCharPattern, static function ($match) use ($that) {
                return $that->getRandomChar($match[0][1]);
            }, $pattern);

            if (
                \is_string($voucherCode)
                && (
                    $reservedVouchers->has($voucherCode)
                    || $this->vouchers->has($voucherCode)
                    || $this->hasBadword($voucherCode)
                    || false === $this->validationProcessor->validate($voucherCode)
                )
            ) {
                $i--;
                $maxLoops--;

                continue;
            }

            $maxLoops = $this->config->getMaxLoops();

            $this->vouchers->add($voucherCode);
        }

        return $this->vouchers;
    }

    public function getConfig(): VoucherCodeGeneratorConfig
    {
        return $this->config;
    }

    public function setConfig(VoucherCodeGeneratorConfig $config): self
    {
        $this->config             = $config;
        $this->validNumbersLength = \strlen($config->getValidNumbers());
        $this->validLettersLength = \strlen($config->getValidLetters());
        $this->badwordPattern     = false;

        $badwords = $config->getBadwords();

        if (0 === $badwords->count()) {
            return $this;
        }

        $this->badwordPattern = \sprintf(
            '/%s/',
            \implode('|', $config->getBadwords()->map(function ($el) {
                return $el;
            }))
        );

        return $this;
    }

    /**
     * @param string $type
     *
     * @return string
     * @throws \Exception
     */
    public function getRandomChar(string $type): string
    {
        if (self::NUMBER_PATTERN === $type) {
            return $this->getRandomNumber();
        }

        if (self::LETTER_PATTERN === $type) {
            return $this->getRandomLetter();
        }

        throw new \Exception('Invalid character type');
    }

    private function calculateMaxPossibleVoucherCodes(): float
    {
        $pattern      = $this->config->getPattern();
        $numOfNumbers = \preg_match_all("/%{$this->getNumberPattern()}/", $pattern);
        $numOfLetters = \preg_match_all("/%{$this->getLetterPattern()}/", $pattern);

        return ($this->validNumbersLength ** $numOfNumbers) * ($this->validLettersLength ** $numOfLetters);
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getRandomNumber(): string
    {
        $pos = \random_int(0, $this->validNumbersLength - 1);

        return $this->config->getValidNumbers()[$pos];
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getRandomLetter(): string
    {
        $pos = \random_int(0, $this->validLettersLength - 1);

        return $this->config->getValidLetters()[$pos];
    }

    private function hasBadword(string $string): bool
    {
        if (false === $this->badwordPattern) {
            return false;
        }

        return \preg_match($this->badwordPattern, $string) > 0;
    }

    private function getNumberPattern(): string
    {
        return self::NUMBER_PATTERN;
    }

    private function getLetterPattern(): string
    {
        return self::LETTER_PATTERN;
    }
}
