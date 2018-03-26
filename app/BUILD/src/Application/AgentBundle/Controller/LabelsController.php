<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

/**
 * Handles label definitions.
 */
class LabelsController extends AbstractController
{
    public function listDefinitionsAction()
    {
        $rep = $this->em->getRepository('DeskPRO:LabelDef');

        return $this->createJsonResponse($rep->getAllDefinitions());
    }
}
