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
     */
    private $scope;

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
