<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Currency.
 *
 * @ORM\Entity()
 * @ORM\Table(name="currencies", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_currency_code", columns={"currency_code"})
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
class Currency implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="currency_code", length=50, nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $currencyCode;

    /**
     * @ORM\Column(type="string", name="name", length=255, nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="symbol", length=50, nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $symbol;

    /**
     * @ORM\Column(type="integer", name="decimal_places", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $decimalPlaces = 2;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return $this
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setModelField('currencyCode', $currencyCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     *
     * @return $this
     */
    public function setSymbol($symbol)
    {
        $this->setModelField('symbol', $symbol);

        return $this;
    }

    /**
     * @return int
     */
    public function getDecimalPlaces()
    {
        return $this->decimalPlaces;
    }

    /**
     * @return int
     */
    public function getDelimiter()
    {
        return pow(10, $this->getDecimalPlaces());
    }

    /**
     * @param int $decimalPlaces
     *
     * @return $this
     */
    public function setDecimalPlaces($decimalPlaces)
    {
        $this->setModelField('decimalPlaces', $decimalPlaces);

        return $this;
    }
}
