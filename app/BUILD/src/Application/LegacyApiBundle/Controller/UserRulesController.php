<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\UserRules\Form\Type\UserRuleType;
use Application\DeskPRO\UserRules\UserRuleEdit;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class UserRulesController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        /*
         * @var \Application\DeskPRO\UserRules\UserRules
         */
        $user_rules = $this->container->getSystemService('user_rules');

        return $this->createApiResponse(
            [
                 'user_rules' => $user_rules->getAllAsArray(),
            ]
        );
    }

    //##################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        /*
         * @var \Application\DeskPRO\UserRules\UserRules
         */
        $user_rules = $this->container->getSystemService('user_rules');
        $user_rule  = $user_rules->getWithUsergroup($id);

        if (!$user_rule) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(
            [
                 'user_rule' => $user_rule,
            ]
        );
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        /*
         * @var \Application\DeskPRO\UserRules\UserRules
         */
        $user_rules = $this->container->getSystemService('user_rules');

        if ($id) {
            $user_rule = $user_rules->getById($id);

            if (!$user_rule) {
                throw $this->createNotFoundException();
            }
        } else {
            $user_rule = $user_rules->createNew();
        }

        $postData = $this->in->getAll('post');

        $user_rule_edit = new UserRuleEdit($user_rule);

        $form = $this->createForm(new UserRuleType(), $user_rule_edit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'user_rule'), true);

        if ($form->isValid()) {
            $user_rule_edit->save($this->em);
        } else {
            throw ValidationException::create($this->getFormValidationErrorsString($form));
        }

        return $this->createApiResponse(
            [
                 'success' => true,
                 'id'      => $user_rule->id,
            ]
        );
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        /*
         * @var \Application\DeskPRO\UserRules\UserRules
         */
        $user_rules = $this->container->getSystemService('user_rules');
        $user_rule  = $user_rules->getById($id);

        if (!$user_rule) {
            throw $this->createNotFoundException();
        }

        $old_id = $user_rule->id;

        $this->db->beginTransaction();

        try {
            $this->em->remove($user_rule);
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(['old_id' => $old_id]);
    }

    //###################################################################################################################
    // apply
    //###################################################################################################################

    public function applyAction($id, $page_id)
    {
        /*
         * @var \Application\DeskPRO\UserRules\UserRules
         */
        $user_rules = $this->container->getSystemService('user_rules');
        $user_rule  = $user_rules->getById($id);

        if (!$user_rule) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse($user_rules->applyRuleToUsers($user_rule, $page_id));
    }
}
