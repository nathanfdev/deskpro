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

namespace DeskPRO\Bundle\AuditBundle\Storage;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StorageFactory.
 */
class StorageFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * StorageFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return object
     */
    public function createStorage()
    {
        $type      = $this->container->get('settings_resolver')->getGlobalSettings()->get('audit_log.storage');
        $storageId = 'audit_log.storage.'.$type;
        if (!$this->container->has($storageId)) {
            throw new \RuntimeException(sprintf('Couldn\'t find audit log storage with type [ %s ]', $type));
        }

        return $this->container->get($storageId);
    }

    /**
     * @return object
     */
    public function createTransformer()
    {
        $type          = $this->container->get('settings_resolver')->getGlobalSettings()->get('audit_log.storage');
        $transformerId = 'audit_log.transformer.'.$type;
        if (!$this->container->has($transformerId)) {
            throw new \RuntimeException(sprintf('Couldn\'t find audit log transformer with type [ %s ]', $type));
        }

        return $this->container->get($transformerId);
    }
}
