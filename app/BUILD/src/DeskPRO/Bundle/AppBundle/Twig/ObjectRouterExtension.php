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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Twig;

use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;

/**
 * Class ObjectRouterExtension.
 */
class ObjectRouterExtension extends \Twig_Extension
{
    /**
     * @var ObjectRouter
     */
    private $objectRouter;

    /**
     * Constructor.
     *
     * @param ObjectRouter $objectRouter
     */
    public function __construct(ObjectRouter $objectRouter)
    {
        $this->objectRouter = $objectRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'portal_path',
                [$this, 'generatePortalPath']
            ),
            new \Twig_SimpleFunction(
                'portal_url',
                [$this, 'generatePortalUrl']
            ),
            new \Twig_SimpleFunction(
                'agent_path',
                [$this, 'generateAgentPath']
            ),
            new \Twig_SimpleFunction(
                'agent_url',
                [$this, 'generateAgentUrl']
            ),
        ];
    }

    public function generatePortalPath($object, $type = null, array $extra_params = [])
    {
        return $this->objectRouter->getPortalPath($object, $type, $extra_params);
    }

    public function generatePortalUrl($object, $type = null, array $extra_params = [])
    {
        return $this->objectRouter->getPortalUrl($object, $type, $extra_params);
    }

    public function generateAgentPath($object, $type = null, array $extra_params = [])
    {
        return $this->objectRouter->getAgentPath($object, $type, $extra_params);
    }

    public function generateAgentUrl($object, $type = null, array $extra_params = [])
    {
        return $this->objectRouter->getAgentUrl($object, $type, $extra_params);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'object_router';
    }
}
