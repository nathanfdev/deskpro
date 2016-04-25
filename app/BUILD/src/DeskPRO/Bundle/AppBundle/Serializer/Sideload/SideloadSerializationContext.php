<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\Sideload;

use Application\DeskPRO\Entity\Person;
use JMS\Serializer\SerializationContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SideloadSerializationContext.
 */
class SideloadSerializationContext extends SerializationContext
{
    /**
     * @var SideloadStore
     */
    protected $sideload_store;

    /**
     * @var array
     */
    protected $includes;

    /**
     * @var bool
     */
    protected $exclusion_enabled = true;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * SideloadSerializationContext constructor.
     *
     * @param SideloadStore         $sideload_store
     * @param array                 $includes
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(SideloadStore $sideload_store, array $includes, TokenStorageInterface $tokenStorage = null)
    {
        parent::__construct();

        $this->sideload_store = $sideload_store;
        $this->includes       = $includes;
        $this->tokenStorage   = $tokenStorage;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return SideloadSerializationContext
     */
    public static function createContext(ContainerInterface $container)
    {
        $raw_includes   = $container->get('request_stack')->getMasterRequest()->query->get('include');
        $sideload_store = new SideloadStore();

        return new self($sideload_store, self::cleanIncludes($raw_includes), $container->get('security.token_storage'));
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setExclusionEnabled($enabled = true)
    {
        $this->exclusion_enabled = (bool) $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExclusionStrategy()
    {
        if ($this->exclusion_enabled) {
            return parent::getExclusionStrategy();
        }

        return;
    }

    /**
     * @return SideloadStore
     */
    public function getSideloadStore()
    {
        return $this->sideload_store;
    }

    /**
     * @return array
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    public function getMappedClass($className)
    {
        if (isset($this->mapping[$className])) {
            return $this->mapping[$className];
        }

        return false;
    }

    /**
     * @param array $mapping
     *
     * @return $this
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @return Person
     */
    public function getUser()
    {
        return $this->tokenStorage ? $this->tokenStorage->getToken()->getUser() : null;
    }

    /**
     * @param string $requested_includes_string
     *
     * @return array
     */
    protected static function cleanIncludes($requested_includes_string)
    {
        if (null === $requested_includes_string) {
            return [];
        }

        $exploded = explode(',', $requested_includes_string);

        $cleaned = [];

        foreach ($exploded as $type) {
            $type = trim($type);

            if (!empty($type)) {
                $cleaned[] = $type;
            }
        }

        return $cleaned;
    }
}
