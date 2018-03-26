<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\EntityRepository\LabelDef;
use Application\LegacyApiBundle\PermissionStrategy\UserTypePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class LabelsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::AGENT);
    }

    public function listDefinitionsAction($type = null)
    {
        return $this->createApiResponse($type ? $this->rep()->getDefinitionsByType($type) : $this->rep()->getAllDefinitions());
    }

    //###################################################################################################################
    // update
    //###################################################################################################################

    public function updateDefinitionAction()
    {
        if (!$old = $this->in->getArrayValue('old')) {
            throw new NotFoundHttpException();
        }

        if (!$new = $this->in->getArrayValue('new')) {
            throw new NotFoundHttpException();
        }

        if (!isset($new['label_type']) || !isset($new['label']) || !isset($new['color'])) {
            throw new NotFoundHttpException();
        }

        $label = trim($new['label']);
        $type  = trim($new['label_type']);
        $rep   = $this->rep();
        $rep->renameLabelDef($old['label'], $label, $new['color'], $type);
        $rep->updateColorForLabel($type, $label, $new['color']);

        return $this->createApiResponse($rep->getDefinition($type, $label)->toApiData());
    }

    //###################################################################################################################
    // create
    //###################################################################################################################

    public function createDefinitionAction()
    {
        $rep = $this->rep();
        if (!$label = $this->in->getString('label')) {
            throw new NotFoundHttpException();
        }

        if ((!$type = $this->in->getString('label_type')) || !$rep::valid($type)) {
            throw new NotFoundHttpException();
        }

        $color = $this->in->getString('color');

        if (!$definition = $rep->getDefinition($type, $label)) {
            $definition = new \Application\DeskPRO\Entity\LabelDef();
            // primary key
            $definition['label_type'] = $type;
            $definition['label']      = trim($label);
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

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function deleteDefinitionAction()
    {
        $label = $this->in->getString('label');
        $type  = $this->in->getString('label_type');
        try {
            if (!$definition = $this->rep()->getDefinition($type, $label)) {
                throw $this->createNotFoundException();
            }
            $this->rep()->deleteDefinition($definition);
        } catch (\Exception $e) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse([], 200);
    }

    /**
     * @return LabelDef
     */
    protected function rep()
    {
        return $this->em->getRepository('DeskPRO:LabelDef');
    }
}
