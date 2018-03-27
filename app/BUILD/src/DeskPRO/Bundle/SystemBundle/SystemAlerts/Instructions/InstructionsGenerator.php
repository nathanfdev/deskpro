<?php

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
