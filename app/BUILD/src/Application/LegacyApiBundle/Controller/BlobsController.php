<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BlobsController.
 *
 * @ApiModes("all")
 * @ApiTags("apiv1")
 */
class BlobsController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'uploadAction');
        $multi->addPermissionStrategy(new PassPermission(), 'getInfoAction');

        return $multi;
    }

    /**
     * Uploads a new temp file. Note that temp files are removed automatically after some time,
     * so whatever process that uses the file upload must toggle the temp status off.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction()
    {
        $file   = $this->request->files->get('upfile') ?: @reset($this->request->files->all()) ?: null;
        $accept = $this->container->getAttachmentAccepter();

        $context = 'agent';
        if ($this->in->getString('context') == 'user') {
            $context = 'user';
        }

        $error = $accept->getError($file, $context);
        if ($error) {
            $message = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

            return $this->createApiErrorResponse($error['error_code'], $message);
        }

        $blob = $accept->accept($file, true);

        return $this->createApiCreateResponse(array(
            'blob' => $blob->toApiData(),
        ), $this->generateUrl('api'));
    }

    /**
     * get-info.
     *
     * @param $id
     * @param $auth
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getInfoAction($id, $auth)
    {
        $blob = $this->em->find('DeskPRO:Blob', $id);
        if (!$blob || $blob->authcode != $auth) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse(array(
            'blob' => $blob->toApiData(),
        ));
    }

    /**
     * @param $id
     * @param $auth
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id, $auth)
    {
        $blob = $this->em->find('DeskPRO:Blob', $id);
        if (!$blob || $blob->authcode != $auth) {
            throw new NotFoundHttpException();
        }

        $this->em->remove($blob);
        $this->em->flush();

        return $this->createSuccessResponse();
    }
}
