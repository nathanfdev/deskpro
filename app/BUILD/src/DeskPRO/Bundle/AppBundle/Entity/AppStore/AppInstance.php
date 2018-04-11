<?php

namespace DeskPRO\Bundle\AppBundle\Entity\AppStore;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app2_app_instance")
 *
 * @JMS\ExclusionPolicy("all")
 */
class AppInstance implements Domain\ApplicationInstance, EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @ORM\Column(name="`is_installed`", type="boolean", options={"default" = 0}, nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     */
    private $isInstalled = false;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AppStore\App")
     * @ORM\JoinColumn(name="app_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\AppStore\App>")
     *
     * @var App
     */
    private $app;

    /**
     * @ORM\Column(type="string", nullable=false, length=100)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @deprecated
     */
    private $scope = 'agent';

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    private $settings = [];

    /**
     * @ORM\Column(name="secret_key", type="text", nullable=true)
     */
    private $secretKey;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    private $createdAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

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
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param App $app
     *
     * @return $this
     */
    public function setApp(App $app = null)
    {
        $this->setModelField('app', $app);

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     *
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->setModelField('settings', $settings);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        $this->setModelField('scope', $scope);

        return $this;
    }

    /**
     * Returns the system identifier for the application.
     * todo remove, BC.
     *
     * @JMS\VirtualProperty()
     *
     * @return string
     */
    public function getApplicationId()
    {
        return $this->app ? $this->app->getId() : null;
    }

    /**
     * todo remove, BC.
     *
     * @JMS\VirtualProperty()
     *
     * @return array
     */
    public function getTargets()
    {
        if (!$this->app) {
            return [];
        }

        return $this->app->getManifest()->getTargets();
    }

    /**
     * @return mixed
     */
    public function getIsInstalled()
    {
        return $this->isInstalled;
    }

    /**
     * @param bool $isInstalled
     *
     * @return AppInstance
     */
    public function setIsInstalled($isInstalled)
    {
        $this->isInstalled = $isInstalled;

        return $this;
    }
}
