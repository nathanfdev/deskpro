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

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Instructions;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use Twig_Environment;

/**
 * Class InstructionsGenerator.
 */
class InstructionsGenerator
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var array Map of incident class to incident instruction template
     */
    private $incidentTemplates = [];

    /**
     * InstructionsGenerator constructor.
     *
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param Incident $incident
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generate(Incident $incident)
    {
        $class = get_class($incident);
        if (!array_key_exists($class, $this->incidentTemplates)) {
            return 'Instructions are not available';
        }

        return $this->twig->render($this->incidentTemplates[$class], compact('incident'));
    }

    /**
     * @param string $incidentClass
     * @param string $template
     *
     * @throws \Exception
     */
    public function addIncidentTemplate($incidentClass, $template)
    {
        $incidentClass = 'DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\\'.$incidentClass;
        if (!class_exists($incidentClass)) {
            throw new \Exception("Incident class $incidentClass not found");
        }

        $template = 'SystemBundle:SystemAlerts/Incident/'.$template;

        $this->incidentTemplates[$incidentClass] = $template;
    }
}
