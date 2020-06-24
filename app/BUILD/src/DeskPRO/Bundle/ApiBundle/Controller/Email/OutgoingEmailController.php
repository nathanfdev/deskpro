<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Email;

use Application\EmailBundle\Entity\SendmailSource;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EmailController.
 *
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/outgoing-emails")
 */
class OutgoingEmailController extends BaseController
{
    /**
     * @Rest\Get("/{uuid}", requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"})
     *
     * @param Request $request
     * @param string  $uuid
     *
     * @return View
     */
    public function getAction(Request $request, $uuid)
    {
        return View::create($this->wrap($this->findEntity($uuid, $request)), Response::HTTP_OK);
    }

    /**
     * Process a particular email.
     *
     * @Rest\Post("/{uuid}/send",
     *  requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"}
     * )
     *
     * @param $request
     * @param $uuid
     *
     * @return View
     */
    public function sendAction(Request $request, $uuid)
    {
        /** @var SendmailSource $source */
        $source  = $this->findEntity($uuid, $request);
        $isForce = $request->query->has('force');

        $res = $this->processSendmailSource($source, $isForce);
        if ($res instanceof SendmailSource) {
            return View::create($this->wrap($res), Response::HTTP_OK);
        }

        return View::create(null, $res);
    }

    /**
     * @Rest\Post("/batch/send")
     *
     * @param $request
     *
     * @return View
     */
    public function batchSendAction(Request $request)
    {
        $uuids   = $request->get('uuids', []);
        $isForce = $request->request->has('force');

        $res = [];
        foreach ($uuids as $uuid) {
            /** @var SendmailSource $source */
            $source = $this->getManager()->getRepository(SendmailSource::class)->findOneByUuid($uuid);

            if (!$source) {
                $res[$uuid] = Response::HTTP_NOT_FOUND;
                continue;
            }

            try {
                $res[$uuid] = $this->processSendmailSource($source, $isForce);
            } catch (\Exception $e) {
                $res[$uuid] = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        return View::create($res, Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/{uuid}/abort",
     *  requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"}
     * )
     *
     * @param $request
     * @param $uuid
     *
     * @return View
     */
    public function abortAction(Request $request, $uuid)
    {
        /** @var SendmailSource $source */
        $source = $this->findEntity($uuid, $request);
        $source->setStatus(SendmailSource::STATUS_ABORTED);

        $this->persistModel($source);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/{uuid}/retry",
     *  requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"}
     * )
     *
     * @param $request
     * @param $uuid
     *
     * @return View
     */
    public function retryAction(Request $request, $uuid)
    {
        /** @var SendmailSource $source */
        $source = $this->findEntity($uuid, $request);
        $source->setStatus(SendmailSource::STATUS_RETRY);

        $this->persistModel($source);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/{uuid}/log")
     *
     * @param Request $request
     * @param string  $uuid
     *
     * @return View
     */
    public function logAction(Request $request, $uuid)
    {
        /** @var SendmailSource $source */
        $source = $this->findEntity($uuid, $request);
        $log    = null;

        if ($source->getLogBlob()) {
            try {
                $log = $this->get('blob.storage')->copyBlobRecordToString($source->getLogBlob());
                if ($source->getLogBlob()->content_type === 'application/gzip') {
                    $log = gzdecode($log);
                }
            } catch (\Exception $e) {
                $log = "Failed to read log file ({$e->getMessage()})";
            }
        }

        return View::create($this->wrap($log), Response::HTTP_OK);
    }

    /**
     * @param SendmailSource $source
     * @param bool           $isForce
     *
     * @return SendmailSource|string
     */
    protected function processSendmailSource(SendmailSource $source, $isForce)
    {
        if (!$isForce) {
            if ($source->getStatus() === SendmailSource::STATUS_PROCESSING) {
                return Response::HTTP_LOCKED;
            } elseif ($source->getStatus() === SendmailSource::STATUS_COMPLETE) {
                return Response::HTTP_NOT_MODIFIED;
            } elseif (in_array($source->getStatus(), [
                SendmailSource::STATUS_ERROR,
                SendmailSource::STATUS_ABORTED,
            ])) {
                return Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        }

        /** @var \Application\EmailBundle\Queue\QueueProc $proc */
        $proc = $this->get('email.queue_processor');

        try {
            $proc->process($source->toRecordArray());
        } catch (\Exception $e) {
            return Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        $this->getManager()->refresh($source);

        return $source;
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return object
     */
    protected function findEntity($id, Request $request)
    {
        if (!$entity = $this->getManager()->getRepository(SendmailSource::class)->findOneByUuid($id)) {
            throw $this->createNotFoundException($this->createEntityNotFoundExceptionMessage(SendmailSource::class, $id));
        }

        return $entity;
    }

    /**
     * Persist model.
     *
     * This method is called on a valid entity to persist it. May be overwritten in child controllers.
     *
     * @param object        $model
     * @param FormInterface $form
     *
     * @return object The passed model
     */
    protected function persistModel($model, FormInterface $form = null)
    {
        $em = $this->getManager();
        $em->persist($model);
        $em->flush();

        return $model;
    }
}
