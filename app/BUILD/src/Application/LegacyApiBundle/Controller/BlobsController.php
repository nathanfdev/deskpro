<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BlobsController.
 *
 * @ApiModes("all")
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

        $props = [];
        if ($this->in->getString('tag')) {
            switch (trim($this->in->getString('tag'))) {
                case 'ticket_attachment':
                    $props['tag'] = 'ticket_attachment';
                    break;
            }
        }

        $blob = $accept->accept($file, true, $props);

        return $this->createApiCreateResponse([
            'blob' => $blob->toApiData(),
        ], $this->generateUrl('api'));
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

        return $this->createApiResponse([
            'blob' => $blob->toApiData(),
        ]);
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
