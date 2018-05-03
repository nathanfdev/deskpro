<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\TwitterAccounts\Form\Type\TwitterAccountType;
use Application\DeskPRO\TwitterAccounts\TwitterAccountEdit;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class TwitterAccountsController extends AbstractController implements ProtectedControllerInterface
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
         * @var \Application\DeskPRO\TwitterAccounts\TwitterAccounts
         */
        $twitter_accounts = $this->container->getSystemService('twitter_accounts');

        return $this->createApiResponse([
            'twitter_accounts' => $twitter_accounts->getAllWithUserAsArray(),
        ]);
    }

    //##################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        /*
         * @var \Application\DeskPRO\TwitterAccounts\TwitterAccounts
         */
        $twitter_accounts = $this->container->getSystemService('twitter_accounts');
        $twitter_account  = $twitter_accounts->getWithUserById($id);

        if (!$twitter_account) {
            throw $this->createNotFoundException();
        }

        $returnedData               = $twitter_account;
        $returnedData['all_agents'] = $twitter_accounts->getAllAgents();

        return $this->createApiResponse([
            'twitter_account' => $returnedData,
        ]);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    public function saveAction($id)
    {
        /*
         * @var \Application\DeskPRO\TwitterAccounts\TwitterAccounts
         */
        $twitter_accounts = $this->container->getSystemService('twitter_accounts');

        if ($id) {
            $twitter_account = $twitter_accounts->getById($id);
            if (!$twitter_account) {
                throw $this->createNotFoundException();
            }
        } else {
            $twitter_account = $twitter_accounts->createNew();
        }

        $postData = $this->in->getAll('post');

        $twitter_account_edit = new TwitterAccountEdit($twitter_account);

        $form = $this->createForm(new TwitterAccountType(), $twitter_account_edit, ['cascade_validation' => true]);
        $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'twitter_account'), true);

        if ($form->isValid()) {
            $twitter_account_edit->save($this->em);
        } else {
            throw ValidationException::create($this->getFormValidationErrorsString($form));
        }

        return $this->createApiResponse([
            'success' => true,
            'id'      => $twitter_account->id,
        ]);
    }

    //###################################################################################################################
    // remove
    //###################################################################################################################

    public function removeAction($id)
    {
        /*
         * @var \Application\DeskPRO\TwitterAccounts\TwitterAccounts
         */
        $twitter_accounts = $this->container->getSystemService('twitter_accounts');
        $twitter_account  = $twitter_accounts->getById($id);

        if (!$twitter_account) {
            throw $this->createNotFoundException();
        }

        $old_id = $twitter_account->id;

        $this->db->beginTransaction();

        try {
            $this->em->remove($twitter_account);
            $this->em->flush();
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createSuccessResponse(['old_id' => $old_id]);
    }
}
