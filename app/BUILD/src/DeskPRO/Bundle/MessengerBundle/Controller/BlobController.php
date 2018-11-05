<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Attachments\AcceptAttachment;
use Application\DeskPRO\Attachments\RestrictionSet;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\MessengerBundle\Exception\MessengerApiException;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\Blob;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BlobController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 * @Rest\Route("/file")
 */
class BlobController extends AbstractMessengerController
{
    /**
     * @param Request $request
     * @Rest\Post("/upload-image")
     *
     * @return View
     */
    public function imageUploadAction(Request $request)
    {
        $exts = ['gif', 'png', 'jpg', 'jpeg'];
        $set  = new RestrictionSet();
        $set->setAllowedExts($exts);

        return new View($this->wrap($this->upload($request->files->get('file'), $set, 'only_images')));
    }

    /**
     * @param Request $request
     *
     * @Rest\Post("/upload-file")
     *
     * @throws \Exception
     *
     * @return View
     */
    public function fileUploadAction(Request $request)
    {
        $exts = ['gif', 'png', 'jpg', 'jpeg',
                 'pdf', 'doc', 'docx', 'xls', 'csv', 'xlsx', 'txt',
                 'rar', 'zip', 'tar.gz', '7zip', 'gzip', 'bzip',
                 'mp4', 'avi', 'wmv', 'mpeg', 'mov', '3gp', 'flv', ];
        $set = new RestrictionSet();
        $set->setAllowedExts($exts);

        return new View($this->wrap($this->upload($request->files->get('file'), $set, 'only_files')));
    }

    /**
     * @param UploadedFile   $file
     * @param RestrictionSet $set
     * @param string         $setId
     *
     * @return Blob
     */
    protected function upload(UploadedFile $file, RestrictionSet $set, $setId)
    {
        /** @var AcceptAttachment $accept */
        $accept = $this->getContainer()->getAttachmentAccepter();
        $accept->addRestrictionSet($setId, $set);
        $error = $accept->getError($file, $setId);
        if (!$error) {
            $blob = $accept->accept($file, true);

            return new Blob($blob);
        } else {
            $errorMessage = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);
            throw new MessengerApiException(['file' => $errorMessage]);
        }
    }
}
