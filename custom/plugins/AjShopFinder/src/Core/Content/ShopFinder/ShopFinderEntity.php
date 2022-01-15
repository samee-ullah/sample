<?php declare(strict_types=1);

namespace AjShopFinder\Core\Content\ShopFinder;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Country\CountryEntity;

class ShopFinderEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var bool
     */
    protected bool $active;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $street;

    /**
     * @var string
     */
    protected string $postCode;

    /**
     * @var string
     */
    protected string $city;

    /**
     * @var string
     */
    protected string $url;

    /**
     * @var string
     */
    protected string $telephone;

    /**
     * @var string
     */
    protected string $openTimes;

    /**
     * @var CountryEntity
     */
    protected $country;

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getPostCode(): string
    {
        return $this->postCode;
    }

    /**
     * @param string $postCode
     */
    public function setPostCode(string $postCode): void
    {
        $this->postCode = $postCode;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * @return string
     */
    public function getOpenTimes(): string
    {
        return $this->openTimes;
    }

    /**
     * @return CountryEntity
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param CountryEntity|null $country
     */
    public function setCountry(?CountryEntity $country): void
    {
        $this->country = $country;
    }
}
