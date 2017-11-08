<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\Client;
use OAuth2\OAuth2;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OAuthClient.
 *
 * @ORM\Entity()
 * @ORM\Table(name="oauth_clients")
 */
class OAuthClient extends Client implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const CONTEXT_AGENT = 'agent';
    const CONTEXT_USER  = 'user';

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="sys_name", type="string", length=255, unique=true, nullable=true)
     *
     * @var string
     */
    protected $sysName;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="random_id", type="string", length=255)
     *
     * @var string
     */
    protected $randomId;

    /**
     * @ORM\Column(name="secret", type="string", length=255)
     *
     * @var string
     */
    protected $secret;

    /**
     * @ORM\Column(name="context", type="string", length=255)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $context;

    /**
     * @ORM\Column(name="redirect_uris", type="array")
     *
     * @Assert\Count(min="1")
     * @Assert\All({
     *     @Assert\NotBlank(),
     *     @Assert\Url(protocols={".+"})
     * })
     *
     * @var array
     */
    protected $redirectUris = [];

    /**
     * @ORM\Column(name="allowed_grant_types", type="array")
     *
     * @var array
     */
    protected $allowedGrantTypes = [];

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     *
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setModelField('dateCreated', new \DateTime());
    }

    /**
     * @return string
     */
    public function getSysName()
    {
        return $this->sysName;
    }

    /**
     * @param string $sysName
     *
     * @return $this
     */
    public function setSysName($sysName)
    {
        $this->setModelField('sysName', $sysName);

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
     * {@inheritdoc}
     */
    public function setRandomId($randomId)
    {
        $this->setModelField('randomId', $randomId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSecret($secret)
    {
        $this->setModelField('secret', $secret);

        return $this;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->setModelField('context', $context);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectUris(array $redirectUris)
    {
        $this->setModelField('redirectUris', $redirectUris);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedGrantTypes(array $allowedGrantTypes)
    {
        $this->setModelField('allowedGrantTypes', $allowedGrantTypes);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     *
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->setModelField('isEnabled', $isEnabled);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAllowedGrantType($value)
    {
        return $this->setAllowedGrantTypes([$value]);
    }

    /**
     * @return string
     */
    public function getAllowedGrantType()
    {
        $values = array_values($this->allowedGrantTypes);

        return isset($values[0]) ? $values[0] : null;
    }

    /**
     * @return string
     */
    public function getResponseType()
    {
        $grantType = $this->getAllowedGrantType();

        switch ($grantType) {
            case OAuth2::GRANT_TYPE_AUTH_CODE:
                return OAuth2::RESPONSE_TYPE_AUTH_CODE;
            case OAuth2::GRANT_TYPE_IMPLICIT:
                return OAuth2::RESPONSE_TYPE_ACCESS_TOKEN;
        }

        throw new \RuntimeException("Unexpected grant type '$grantType'");
    }
}
