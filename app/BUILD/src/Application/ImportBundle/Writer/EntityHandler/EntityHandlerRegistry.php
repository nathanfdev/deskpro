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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\ImportBundle\Model\ImportModelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityHandlerRegistry.
 */
class EntityHandlerRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $handlers = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $handlers
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param ImportModelInterface $model
     *
     * @throws \Exception
     *
     * @return EntityHandlerInterface
     */
    public function getHandlers(ImportModelInterface $model)
    {
        $modelClass = get_class($model);

        if (!isset($this->handlers[$modelClass])) {
            throw new \Exception("Importer entity handler with $model not found");
        }

        $handlers = [];
        foreach ($this->handlers[$modelClass] as $id) {
            $handlers[] = $this->container->get($id);
        }

        return $handlers;
    }
}
