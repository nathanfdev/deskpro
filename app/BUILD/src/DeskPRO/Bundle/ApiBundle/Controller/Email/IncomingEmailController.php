<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Email;

use Application\DeskPRO\Email\EmailSource\PropertyMapper;
use Application\DeskPRO\EmailGateway\Runner;
use Application\DeskPRO\Entity\EmailSource;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Validator\ValidatorErrorsException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmailController.
 *
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/incoming-emails")
 */
class IncomingEmailController extends BaseController
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
     * @Rest\Post("/{uuid}/execute", requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"}, name="api_v2_incoming_email_execute")
     *
     * @param $request
     * @param $uuid
     *
     * @return View
     */
    public function executeAction(Request $request, $uuid)
    {
        /** @var EmailSource $source */
        $source  = $this->findEntity($uuid, $request);
        $isForce = $request->query->has('force');

        if (!$isForce) {
            if ($source->getStatus() === EmailSource::STATUS_PROCESSING) {
                return View::create(null, Response::HTTP_LOCKED);
            } elseif ($source->getStatus() === EmailSource::STATUS_COMPLETE) {
                return View::create(null, Response::HTTP_NOT_MODIFIED);
            } elseif (in_array($source->getStatus(), [
                EmailSource::STATUS_REJECTED,
                EmailSource::STATUS_ERROR,
                EmailSource::STATUS_REJECTED_SOFT,
            ])) {
                return View::create(null, Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $runner = new Runner();

        // Previous attempt might've failed with a fatal, so we need to check
        // if its still in processing state now and potentially cancel it as error'd now
        if (
            !$isForce
            && ($source->getStatus() === EmailSource::STATUS_INSERTED || $source->getStatus() === EmailSource::STATUS_PROCESSING)
            && $runner->getMaxRetryAttempts() < $source->getExecCount()
        ) {
            return View::create(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // read the raw source to refresh some fields on the email source and adjust the blob size
        $blob = $source->getBlob();

        $source->getRawSource();
        if (empty($source->_raw)) {
            throw new \Exception('Unexpected empty email source. Maybe downloading failed');
        }
        $fs = strlen($source->_raw);
        if ($fs !== $blob->getFilesize()) {
            $blob->setFilesize($fs);
            $this->getContainer()->getEm()->persist($source);
            $this->getContainer()->getEm()->flush();
        }

        $accountManager = $this->getContainer()->getEmailAccountManager();
        $readerFactory  = $this->getContainer()->getEmailEzcReaderFactory();

        $mapper = new PropertyMapper($accountManager, $readerFactory);
        $reader = $mapper->createReader($source->_raw);
        $mapper->read($reader, $source);
        $source->fromArray([
            'status'      => EmailSource::STATUS_INSERTED,
            'object_type' => EmailSource::OBJ_TYPE_TICKET,
        ]);

        $this->getContainer()->getEm()->persist($source);
        $this->getContainer()->getEm()->flush();

        $runner->executeSource($source, $reader);

        return View::create($this->wrap($source), Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/{uuid}/abort", requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"})
     *
     * @param $request
     * @param $uuid
     *
     * @return View
     */
    public function abortAction(Request $request, $uuid)
    {
        /** @var EmailSource $source */
        $source = $this->findEntity($uuid, $request);
        $source->setStatus(EmailSource::STATUS_REJECTED);

        $this->persistModel($source);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/{uuid}/retry", requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"})
     *
     * @param $request
     * @param $uuid
     *
     * @return View
     */
    public function retryAction(Request $request, $uuid)
    {
        /** @var EmailSource $source */
        $source = $this->findEntity($uuid, $request);
        $source->setStatus(EmailSource::STATUS_RETRY);

        $this->persistModel($source);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/{uuid}/log", requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"})
     *
     * @param Request $request
     * @param string  $uuid
     *
     * @return View
     */
    public function logAction(Request $request, $uuid)
    {
        /** @var EmailSource $source */
        $source = $this->findEntity($uuid, $request);
        $log    = null;

        if ($source->log_blob) {
            try {
                $log = $this->get('blob.storage')->copyBlobRecordToString($source->log_blob);
                if ($source->log_blob->content_type === 'application/gzip') {
                    $log = gzdecode($log);
                }
            } catch (\Exception $e) {
                $log = "Failed to read log file ({$e->getMessage()})";
            }
        }

        return View::create($this->wrap($log), Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/{uuid}",
     *  condition="request.headers.get('Content-Type') matches '#message/rfc822#i'",
     *  requirements={"uuid"="^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$"}
     * )
     *
     * @param $request
     *
     * @return View
     */
    public function postAction(Request $request, $uuid)
    {
        if ($this->getManager()->getRepository(EmailSource::class)->findOneByUuid($uuid)) {
            return View::create(null, Response::HTTP_CONFLICT);
        }

        $eml = $request->getContent();

        $errors = $this->get('validator')->validate($eml, [
            new Assert\NotBlank(),
        ]);

        if ($errors->count() > 0) {
            throw new ValidatorErrorsException($errors);
        }

        // Save blob
        $blobStorage = $this->get('blob.storage');
        $blob        = $blobStorage->createBlobRecordFromString(
            $eml,
            'email.eml',
            'message/rfc822'
        );
        $blob->setFilesize(strlen($eml));

        // Save EmailSource
        $source         = new EmailSource();
        $source['blob'] = $blob;
        $source->fromArray([
            'uuid'           => $uuid,
            'headers'        => '',
            'status'         => EmailSource::STATUS_INSERTED,
            'from_email'     => '',
            'header_to'      => '',
            'header_cc'      => '',
            'header_from'    => '',
            'header_subject' => '',
            'object_type'    => EmailSource::OBJ_TYPE_TICKET,
        ]);

        $this->persistModel($source);

        return View::create($this->wrap($source), Response::HTTP_CREATED);
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return object
     */
    protected function findEntity($id, Request $request)
    {
        if (!$entity = $this->getManager()->getRepository(EmailSource::class)->findOneByUuid($id)) {
            throw $this->createNotFoundException($this->createEntityNotFoundExceptionMessage(EmailSource::class, $id));
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
