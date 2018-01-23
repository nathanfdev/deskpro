<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ReportsRendererRegistry.
 */
class ReportsRendererRegistry
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $renderers = [];

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
     * @param string $type
     * @param string $outputFormat
     * @param string $id
     *
     * @return $this
     */
    public function addRenderer($type, $outputFormat, $id)
    {
        $this->renderers[$type][$outputFormat] = $id;

        return $this;
    }

    /**
     * @param string $type
     * @param string $outputFormat
     *
     * @throws \Exception
     *
     * @return ReportsRendererInterface
     */
    public function getRenderer($type, $outputFormat)
    {
        $type         = strtolower($type);
        $outputFormat = strtolower($outputFormat);

        if (isset($this->renderers[$type][$outputFormat])) {
            return $this->container->get($this->renderers[$type][$outputFormat]);
        } else {
            throw new \Exception("Unknown report renderer $type $outputFormat specified.");
        }
    }
}
