<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\CloudEmails;

use Application\DeskPRO\BlobStorage\StorageAdapter\AmazonS3Storage;
use Application\DeskPRO\Email\EmailSource\PropertyMapper;
use Application\DeskPRO\EmailGateway\Runner;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\EmailSource;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IncomingEmailController.
 *
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/cloud-emails/incoming-email")
 */
class IncomingEmailController extends BaseController
{
    /**
     * @Rest\Post("")
     *
     * @param $request
     *
     * @throws \Application\DeskPRO\BlobStorage\BlobStorageException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return DTOEmailMessage
     */
    public function createEmailSource(Request $request)
    {
        /** @var \JMS\Serializer\SerializerInterface $serializer */
        $serializer = $this->get('serializer');
        /** @var DTOEmailMessage $dto */
        $dto = $serializer->deserialize($request->getContent(), DTOEmailMessage::class, 'json');

        $blobStorage = $this->getContainer()->getBlobStorage();
        $tmpMsg      = sprintf('Email message %s', $dto->getMessageId());
        $blob        = $blobStorage->createBlobRecordFromString(
            $tmpMsg,
            $dto->getMessageId(),
            'message/rfc822',
            ['storage_loc_specific' => 's3']
        );

        // TODO receive the filesize from the client
        $blob->setFilesize(strlen($tmpMsg));

        $source         = new EmailSource();
        $source['blob'] = $blob;
        $source->fromArray([
            'headers'        => '',
            'status'         => EmailSource::STATUS_INSERTING,
            'from_email'     => '',
            'header_to'      => '',
            'header_cc'      => '',
            'header_from'    => '',
            'header_subject' => '',
            'object_type'    => EmailSource::OBJ_TYPE_TICKET,
        ]);

        $this->getContainer()->getEm()->persist($source);
        $this->getContainer()->getEm()->flush();

        // TODO the source id should not replace the message id
        $dto->setMessageId($source->getId());

        /** @var AmazonS3Storage $s3Adapter */
        $s3Adapter = $blobStorage->getAdapter('s3');
        $location  = new DTOMessageLocationS3();
        $location->setBucketName(
            $s3Adapter->getOption('bucket')
        );
        $location->setObjectKey(
            $s3Adapter->resolvePath($blob->getSavePath())
        );
        $dto->setLocation($location);

        return $dto;
    }

    /**
     * Process a particular email.
     *
     * Any response >299 will be interpreted  by the worker as "please retry it".
     * So we return success responses in error cases such as rejection or too-many-retries
     * to make the worker stop trying.
     *
     * @Rest\Post("/{id}/process")
     *
     * @param $request
     * @param $id
     *
     * @throws \Exception
     *
     * @return array|JsonResponse
     */
    public function processSource(Request $request, $id)
    {
        /** @var EmailSource $source */
        $source = $this->findOr404(EmailSource::class, $id);
        $runner = new Runner();

        // Previous attempt might've failed with a fatal, so we need to check
        // if its still in processing state now and potentially cancel it as error'd now
        if (
            ($source->getStatus() === EmailSource::STATUS_INSERTED || $source->getStatus() === EmailSource::STATUS_PROCESSING)
            && $runner->getMaxRetryAttempts() > $source->getExecCount()
        ) {
            $source->setStatus(EmailSource::STATUS_ERROR);
            $source->setErrorCode(EmailSource::ERR_SERVER_ERROR);

            return ['status' => $source->getStatus(), 'messageId' => $id];
        }

        // Already done, dont want to do it again
        if (
            $source->getStatus() === EmailSource::STATUS_COMPLETE
            || $source->getStatus() === EmailSource::STATUS_REJECTED
            || $source->getStatus() === EmailSource::STATUS_REJECTED_SOFT
        ) {
            return ['status' => $source->getStatus(), 'messageId' => $id];
        }

        // read the raw source to refresh some fields on the email source and adjust the blob size
        $blob = $source->getBlob();

        $source->_raw = $this->getContainer()->getBlobStorage()->downloadBlobData($blob);
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
        $ret = ['status' => $source->getStatus(), 'messageId' => $id];

        if ($source->getStatus() === EmailSource::STATUS_RETRY) {
            return new JsonResponse($ret, Response::HTTP_SERVICE_UNAVAILABLE);
        } else {
            return $ret;
        }
    }
}
