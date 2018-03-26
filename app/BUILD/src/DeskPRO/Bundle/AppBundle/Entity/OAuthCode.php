<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\AuthCode;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OAuthCode.
 *
 * @ORM\Entity()
 * @ORM\Table(name="oauth_codes")
 */
class OAuthCode extends AuthCode implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\OAuthClient")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var OAuthClient
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @var Person
     */
    protected $user;

    /**
     * @ORM\Column(name="token", type="string", length=255, unique=true)
     *
     * @var string
     */
    protected $token;

    /**
     * @ORM\Column(name="expires_at", type="integer", nullable=true)
     *
     * @var int
     */
    protected $expiresAt;

    /**
     * @ORM\Column(name="scope", type="string", length=255, nullable=true)
     *
     * @var string
     */
    protected $scope;

    /**
     * @ORM\Column(name="redirect_uri", type="string", length=255, nullable=true)
     *
     * @var string
     */
    protected $redirectUri;

    /**
     * {@inheritdoc}
     */
    public function setClient(ClientInterface $client)
    {
        $this->setModelField('client', $client);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user)
    {
        $this->setModelField('user', $user);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken($token)
    {
        $this->setModelField('token', $token);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiresAt($expiresAt)
    {
        $this->setModelField('expiresAt', $expiresAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        $this->setModelField('scope', $scope);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectUri($redirectUri)
    {
        $this->setModelField('redirectUri', $redirectUri);

        return $this;
    }
}
