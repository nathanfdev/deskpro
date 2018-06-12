<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\AccessTokenInterface;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OAuthAccessToken.
 *
 * @ORM\Entity()
 * @ORM\Table(name="oauth_access_tokens")
 */
class OAuthAccessToken implements EntityInterface, AccessTokenInterface, NotifyPropertyChanged
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
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var OAuthClient
     */
    protected $client;

    /**
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\ApiToken", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="api_token_id", nullable=false, onDelete="CASCADE")
     *
     * @var ApiToken
     */
    protected $apiToken;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->apiToken = new ApiToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->getClient()->getPublicId();
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiresAt($timestamp)
    {
        $this->apiToken->setDateExpires(new \DateTime('@'.$timestamp));
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresAt()
    {
        $dateExpires = $this->apiToken->getDateExpires();

        return $dateExpires ? $dateExpires->getTimestamp() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        $expiresAt = $this->getExpiresAt();
        if ($expiresAt) {
            return $expiresAt - time();
        }

        return PHP_INT_MAX;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpired()
    {
        $expiresAt = $this->getExpiresAt();
        if ($expiresAt) {
            return time() > $expiresAt;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken($token)
    {
        $this->apiToken->setToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->apiToken->getToken();
    }

    /**
     * @return ApiToken
     */
    public function getTokenEntity()
    {
        return $this->apiToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        if (!$scope) {
            $scope = '';
        }

        $this->apiToken->setScope($scope);
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->apiToken->getScope();
    }

    /**
     * {@inheritdoc}
     *
     * @param Person $user
     */
    public function setUser(UserInterface $user)
    {
        $this->apiToken->setPerson($user);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->apiToken->getPerson();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getUser();
    }

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
    public function getClient()
    {
        return $this->client;
    }
}
