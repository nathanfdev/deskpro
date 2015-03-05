<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\UserTypePermission;
use Application\DeskPRO\EntityRepository\LabelDef;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LabelsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::AGENT);
    }

    public function listDefinitionsAction()
    {
        return $this->createApiResponse($this->rep()->getAllDefinitions());
    }

    ####################################################################################################################
    # update
    ####################################################################################################################

    public function updateDefinitionAction()
    {
        if (!$old = $this->in->getArrayValue('old')) {
            throw new NotFoundHttpException;
        }

        if (!$new = $this->in->getArrayValue('new')) {
            throw new NotFoundHttpException;
        }

        if (!isset($new['label_type']) || !isset($new['label']) || !isset($new['color'])) {
            throw new NotFoundHttpException;
        }

        $label = trim($new['label']);
        $type = trim($new['label_type']);
        $rep = $this->rep();
        $rep->renameLabelDef($old['label'], $label, $new['color'], $type);
        $rep->updateColorForLabel($type, $label, $new['color']);

        return $this->createApiResponse($rep->getDefinition($type, $label)->toApiData());
    }

    ####################################################################################################################
    # create
    ####################################################################################################################

    public function createDefinitionAction()
    {
        $rep = $this->rep();
        if (!$label = $this->in->getString('label')) {
            throw new NotFoundHttpException;
        }

        if ((!$type = $this->in->getString('label_type')) || !$rep::valid($type)) {
            throw new NotFoundHttpException;
        }

        $color = $this->in->getString('color');

        if (!$definition = $rep->getDefinition($type, $label)) {
            $definition = new \Application\DeskPRO\Entity\LabelDef();
            // primary key
            $definition['label_type'] = $type;
            $definition['label'] = trim($label);
            $this->em->persist($definition);

        } else {
            $rep->renameLabelDef($definition['label'], trim($label), $color, $type);
        }

        $definition['label'] = trim($label);
        $definition['color'] = $color;
        $rep->updateDefinitionUsages($definition);
        $rep->updateColorForLabel($type, $label, $color);
        $this->em->flush();

        return $this->createApiResponse($definition->toApiData());
    }


    ####################################################################################################################
    # remove
    ####################################################################################################################

    public function deleteDefinitionAction()
    {
        $label = $this->in->getString('label');
        $type = $this->in->getString('label_type');
        try {
            if (!$definition = $this->rep()->getDefinition($type, $label)) {
                throw $this->createNotFoundException();
            }
            $this->rep()->deleteDefinition($definition);
        } catch (\Exception $e) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(array(), 200);
    }

    /**
     * @return LabelDef
     */
    protected function rep()
    {
        return $this->em->getRepository('DeskPRO:LabelDef');
    }
}
