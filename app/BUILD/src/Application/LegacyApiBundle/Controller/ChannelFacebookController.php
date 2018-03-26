<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\FacebookApp;
use Application\DeskPRO\Entity\FacebookPage;
use Application\DeskPRO\Facebook\EditPage;
use Application\DeskPRO\Facebook\Type\EditPageType;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class ChannelFacebookController.
 *
 * @ApiModes("all")
 */
class ChannelFacebookController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }

    //###################################################################################################################
    // list facebook pages
    //###################################################################################################################

    public function listAction()
    {
        $pages = $this->getFacebookPageRepo()->findAll();

        $data = $this->getContainer()->getSerializer()->serializeArray($pages);

        return $this->createApiResponse(['facebook_pages' => $data]);
    }

    //###################################################################################################################
    // create a facebook page
    //###################################################################################################################

    public function createAction()
    {
        $page_postdata = $this->in->getArrayValue('page');

        if (!isset($page_postdata['graph_id'])) {
            return $this->createApiErrorResponse('invalid_argument', 'graph_id of a page is required');
        }

        $fb_app_repo   = $this->container->getEm()->getRepository('DeskPRO:FacebookPage');
        $existing_page = $fb_app_repo->findOneBy(['graph_id' => $page_postdata['graph_id']]);

        if ($existing_page) {
            return $this->createApiErrorResponse('page_exists', 'this page already exists as a channel');
        }

        try {
            $existing_app = null;
            if (isset($page_postdata['app']) && isset($page_postdata['app']['app_id'])) {
                $fb_app_repo  = $this->container->getEm()->getRepository('DeskPRO:FacebookApp');
                $existing_app = $fb_app_repo->findOneBy(['app_id' => $page_postdata['app']['app_id']]);
            }

            $page      = new FacebookPage();
            $page->app = $existing_app ?: new FacebookApp();

            $model = new EditPage($page);
            $form  = $this->createForm(new EditPageType(), $model);
            $form->submit($page_postdata, true);

            $model->save($this->container->getEm());

            $data = $this->getContainer()->getSerializer()->serialize($page);

            return $this->createApiSuccessResponse($data);
        } catch (\Exception $e) {
            throw $e;

            return $this->createApiErrorResponse('invalid_argument', 'bad request - please check app credentials and retry');
        }
    }

    //###################################################################################################################
    // get facebook page
    //###################################################################################################################

    public function getAction($id)
    {
        $page = $this->getFacebookPageRepo()->find($id);

        if (!$page) {
            return $this->createApiErrorResponse('not_found', sprintf('facebook page (id=%s) does not exist', $id));
        }

        $data = $this->getContainer()->getSerializer()->serialize($page);

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // save facebook page
    //###################################################################################################################

    public function saveAction($id = null)
    {
        if (!$id) {
            return $this->createApiErrorResponse('invalid_argument', 'ID not passed');
        }

        $page = $this->getFacebookPageRepo()->find($id);

        if (!$page) {
            return $this->createApiErrorResponse('facebook.page_not_found', 'facebook page not found', 404);
        }

        $model = new EditPage($page);
        $form  = $this->createForm(new EditPageType(), $model);

        $page_postdata = $this->in->getArrayValue('page');
        $form->submit($page_postdata, true);

        $model->save($this->container->getEm());
        $data = $this->getContainer()->getSerializer()->serialize($page);

        return $this->createApiSuccessResponse($data);
    }

    //###################################################################################################################
    // delete facebook page
    //###################################################################################################################

    public function deleteAction($id)
    {
        $page = $this->getFacebookPageRepo()->find($id);

        if (!$page) {
            return $this->createApiErrorResponse('not_found', sprintf('facebook page (id=%s) does not exist', $id));
        }

        $em = $this->getContainer()->getEm();
        $em->remove($page);
        $em->flush();

        return $this->createApiSuccessResponse();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getFacebookPageRepo()
    {
        return $this->getContainer()->getEm()->getRepository('DeskPRO:FacebookPage');
    }

    /**
     * @param $account
     */
    protected function saveFacebookPage(FacebookPage $account)
    {
        $this->getContainer()->getEm()->persist($account);
        $this->getContainer()->getEm()->flush();
    }
}
