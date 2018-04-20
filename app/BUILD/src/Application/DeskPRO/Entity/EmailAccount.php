<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\PhpMailConfig;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\SmtpConfig;
use DeskPRO\Component\Util\IpUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Arrays;

/**
 * @property int $id
 * @property string $account_type
 * @property \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface $incoming_account
 * @property \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface $outgoing_account
 * @property bool $is_enabled
 * @property string $address
 * @property array $other_addresses
 * @property array $options
 * @property Brand[]|ArrayCollection $brands
 * @property bool $is_all_brands
 * @property \DateTime $date_created
 * @property \DateTime $date_read_start
 * @property \DateTime $date_last_incoming
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class EmailAccount extends DomainObject
{
    /**
     * This is an outgoing account only (no incoming reading ability).
     */
    const TYPE_OUT = 'outgoing';

    /**
     * This is a ticket gateway account.
     */
    const TYPE_TICKETS = 'tickets';

    /**
     * This is an article gateway account.
     */
    const TYPE_ARTICLES = 'artices';

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $account_type;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
     */
    protected $incoming_account = null;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
     */
    protected $outgoing_account = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $address;

    /**
     * @var array
     */
    protected $other_addresses = [];

    /**
     * Misc options or flags that can be used by whatever uses this account.
     *
     * @var array
     */
    protected $options;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $brands;

    /**
     * True if enabled for all brands.
     *
     * @var bool
     */
    protected $is_all_brands = true;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \DateTime
     */
    protected $date_read_start;

    /**
     * @var \DateTime
     */
    protected $date_last_incoming;

    /**
     * @var bool
     */
    protected $is_read_active = false;

    /**
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $cert_blob = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $key_blob = null;

    /**
     * @var string
     */
    protected $key_pass_phrase = null;

    /**
     * @param string $account_type
     */
    public function __construct($account_type = null)
    {
        if ($account_type) {
            $this->setAccountType($account_type);
        }

        $this->date_created    = new \DateTime();
        $this->date_read_start = new \DateTime();
        $this->brands          = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->setModelField('address', $address);

        return $this;
    }

    /**
     * @param string $account_type
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setAccountType($account_type)
    {
        if (!in_array($account_type, [
            self::TYPE_OUT,
            self::TYPE_TICKETS,
            self::TYPE_ARTICLES,
        ])
        ) {
            throw new \InvalidArgumentException();
        }

        $this->setModelField('account_type', $account_type);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIncomingAccountType()
    {
        if (!$this->incoming_account) {
            return null;
        }

        return $this->incoming_account->getType();
    }

    /**
     * @return null|string
     */
    public function getOutgoingAccountType()
    {
        if (!$this->outgoing_account) {
            return null;
        }

        return $this->getOutgoingAccount()->getType();
    }

    /**
     * @return \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
     */
    public function getOutgoingAccount()
    {
        $account = $this->outgoing_account;

        // On cloud, should never be a local host so re-write these as using the mailer
        if (defined('DPC_IS_CLOUD')) {
            if ($account instanceof SmtpConfig && IpUtils::guessIsLocalNetworkHost($account->host)) {
                $account = new PhpMailConfig();
            }
        }

        return $account;
    }

    /**
     * @return \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
     */
    public function getRealOutgoingAccount()
    {
        return $this->outgoing_account;
    }

    /**
     * Get an array of all of the addresses for this account.
     *
     * @return array
     */
    public function getAllAddresses()
    {
        $addrs = $this->other_addresses ?: [];
        array_unshift($addrs, $this->address);

        return $addrs;
    }

    /**
     * @param string $address
     *
     * @return bool
     */
    public function hasAddress($address)
    {
        $address = strtolower($address);

        if ($this->address == $address) {
            return true;
        }

        if ($this->other_addresses) {
            foreach ($this->other_addresses as $addr) {
                if ($addr == $address) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Given an address, see if it matches in this account and return the matched address.
     *
     * At the moment this method only handles exact matches, but theres a possibility it could be extended
     * to allow for patterns.
     *
     * @param string $address
     *
     * @return null|string
     */
    public function getEmailAddressMatch($address)
    {
        $address = strtolower($address);

        if ($this->getUseEmailAddress() == $address) {
            return $address;
        }

        foreach ($this->other_addresses as $addr) {
            if ($addr == $address) {
                return $address;
            }
        }

        return null;
    }

    /**
     * Gets the real address to use for this account.
     * E.g., this account might have many addresses and aliases, this is the one to use
     * by default for outgoing messages.s.
     *
     * @return string
     */
    public function getUseEmailAddress()
    {
        if ($this->options && !empty($this->options['custom_email_address'])) {
            return $this->options['custom_email_address'];
        }

        return $this->address;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if (!$this->options || !isset($this->options[$name])) {
            return $default;
        }

        return $this->options[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $new = $this->options;

        if ($value === null) {
            if (!$new) {
                return $this;
            }

            unset($new[$name]);
            if (!$new) {
                $this->options = null;
            }
        } else {
            if (!$new) {
                $new = [];
            }
            $new[$name] = $value;
        }

        $this->setModelField('options', $new);

        return $this;
    }

    /**
     * @param string $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        if (!$options) {
            $options = null;
        }

        $this->setModelField('options', $options);

        return $this;
    }

    /**
     * @return ArrayCollection|Brand[]
     */
    public function getBrands()
    {
        return $this->brands;
    }

    /**
     * @param Brand $brand
     *
     * @return self
     */
    public function addBrand(Brand $brand)
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
            $this->_onPropertyChanged('brands', $this->brands, $this->brands);
        }

        return $this;
    }

    /**
     * @param Brand $brand
     *
     * @return self
     */
    public function removeBrand(Brand $brand)
    {
        $this->brands->removeElement($brand);
        $this->_onPropertyChanged('brands', $this->brands, $this->brands);

        return $this;
    }

    /**
     * @param Brand|int $brand
     *
     * @return bool
     */
    public function hasBrand($brand)
    {
        if ($brand instanceof Brand) {
            return $this->brands->contains($brand);
        }

        $brand = (int) $brand;
        foreach ($this->brands as $brand) {
            if ($brand->getId() === $brand) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return $this
     */
    public function clearBrands()
    {
        $this->brands->clear();
        $this->_onPropertyChanged('brands', $this->brands, $this->brands);

        return $this;
    }

    /**
     * @param bool $is_enabled
     */
    public function setIsAllBrands($is_all_brands)
    {
        $this->setModelField('is_all_brands', $is_all_brands);
    }

    /**
     * @return bool
     */
    public function isAllBrands()
    {
        return (bool) $this->is_all_brands;
    }

    /**
     * @return Blob
     */
    public function getCertBlob()
    {
        return $this->cert_blob;
    }

    /**
     * @param Blob $certBlob
     *
     * @return $this
     */
    public function setCertBlob($certBlob)
    {
        $this->setModelField('cert_blob', $certBlob);

        return $this;
    }

    /**
     * @return Blob
     */
    public function getKeyBlob()
    {
        return $this->key_blob;
    }

    /**
     * @param Blob $keyBlob
     *
     * @return $this
     */
    public function setKeyBlob($keyBlob)
    {
        $this->setModelField('key_blob', $keyBlob);

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyPassPhrase()
    {
        return $this->key_pass_phrase;
    }

    /**
     * @param string $keyPassPhrase
     *
     * @return EmailAccount
     */
    public function setKeyPassPhrase($keyPassPhrase)
    {
        $this->setModelField('key_pass_phrase', $keyPassPhrase);

        return $this;
    }

    //###########################################################################
    // Export
    //###########################################################################

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        $data['incoming_account']      = $this->incoming_account ? $this->incoming_account->serializeJsonArray() : [];
        $data['incoming_account_type'] = $this->getIncomingAccountType();
        $data['outgoing_account_type'] = $this->getOutgoingAccountType();
        $data['outgoing_account']      = $this->outgoing_account ? $this->getOutgoingAccount()->serializeJsonArray() : [];
        $data['use_email_address']     = $this->getUseEmailAddress();

        $data['brand_ids'] = [];
        foreach ($this->brands as $brand) {
            $data['brand_ids'][] = $brand->getId();
        }
        $data['brand_ids'] = Arrays::castToType($data['brand_ids'], 'int');

        return $data;
    }

    public function __toString()
    {
        return sprintf('<EmailAccount:%d> %s', $this->id, implode(', ', $this->getAllAddresses()));
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\\DeskPRO\\EntityRepository\\EmailAccount';

        $metadata->setPrimaryTable([
            'name' => 'email_accounts',
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'account_type',
            'fieldName'  => 'account_type',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'incoming_account',
            'fieldName'  => 'incoming_account',
            'type'       => 'dp_json_obj',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'outgoing_account',
            'fieldName'  => 'outgoing_account',
            'type'       => 'dp_json_obj',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'is_enabled',
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'address',
            'fieldName'  => 'address',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'other_addresses',
            'fieldName'  => 'other_addresses',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'options',
            'fieldName'  => 'options',
            'type'       => 'json_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'date_created',
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'date_read_start',
            'fieldName'  => 'date_read_start',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'date_last_incoming',
            'fieldName'  => 'date_last_incoming',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'is_read_active',
            'fieldName'  => 'is_read_active',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'brands',
                'targetEntity' => Brand::class,
                'cascade'      => ['persist', 'merge'],
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'joinTable'    => [
                    'name'        => 'email_account_to_brand',
                    'schema'      => null,
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'email_account_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => false,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                    'inverseJoinColumns' => [
                        0 => [
                            'name'                 => 'brand_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => false,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                ],
            ]
        );

        $metadata->mapField([
            'fieldName'  => 'is_all_brands',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_all_brands',
            'options'    => ['default' => '1'],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'cert_blob',
            'targetEntity' => Blob::class,
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => [
                [
                    'name'                 => 'cert_blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'key_blob',
            'targetEntity' => Blob::class,
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'joinColumns'  => [
                [
                    'name'                 => 'key_blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);
        $metadata->mapField([
            'columnName' => 'key_pass_phrase',
            'fieldName'  => 'key_pass_phrase',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => true,
        ]);
    }
}
