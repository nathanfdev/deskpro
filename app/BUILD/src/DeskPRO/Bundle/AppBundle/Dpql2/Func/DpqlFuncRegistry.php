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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Func;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DpqlFuncFactory.
 */
class DpqlFuncRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DpqlFunctionInterface[]
     */
    private $functions = [];

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
     * @param string $name
     * @param string $id
     *
     * @return $this
     */
    public function addFunction($name, $id)
    {
        $this->functions[$name] = $id;

        return $this;
    }

    /**
     * Returns the correct function handler object.
     *
     * @param string $name
     *
     * @return DpqlFunctionInterface
     */
    public function getFunction($name)
    {
        $name = strtoupper($name);
        if (isset($this->functions[$name])) {
            return $this->container->get($this->functions[$name]);
        } else {
            return new SqlPass($name);
        }
    }

    /**
     * @return Link
     */
    public function getLinkFunction()
    {
        return $this->getFunction('link');
    }
}
