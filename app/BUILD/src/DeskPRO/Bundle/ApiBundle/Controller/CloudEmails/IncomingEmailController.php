<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\CloudEmails;

use Application\DeskPRO\BlobStorage\StorageAdapter\AmazonS3Storage;
use Application\DeskPRO\Email\EmailSource\PropertyMapper;
use Application\DeskPRO\EmailGateway\Runner;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\EmailSource;
use Aws\Credentials\CredentialProvider;
use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IncomingEmailController
 * @package DeskPRO\Bundle\ApiBundle\Controller\CloudEmails
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/cloud-emails/incoming-email")
 */
class IncomingEmailController extends BaseController
{
    /**
     * @Rest\Post("")
     * @param $request
     * @return DTOEmailMessage
     * @throws \Application\DeskPRO\BlobStorage\BlobStorageException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    public function createEmailSource( Request $request)
    {
        /** @var \JMS\Serializer\SerializerInterface $serializer */
        $serializer = $this->get('serializer');
        /** @var DTOEmailMessage $dto */
        $dto = $serializer->deserialize($request->getContent(), DTOEmailMessage::class, 'json');

        $blobStorage = $this->getContainer()->getBlobStorage();
        $blob = $blobStorage->createBlobRecordFromString(
            sprintf("CloudEmail message %s",$dto->getMessageId()),
            $dto->getMessageId(),
            'message/rfc822',
            [ 'storage_loc_specific' => "s3" ]
        );
        // TODO receive the filesize from the client
        $blob->setFilesize(10);

        $source    = new EmailSource();
        $source["blob"] = $blob;
        $source->fromArray([
            'headers'       => "",
            'status'        => EmailSource::STATUS_INSERTING,
            'from_email'    => "",
            'header_to'    => "",
            'header_cc'    => "",
            'header_from'    => "",
            'header_subject'    => "",
            'object_type'    => EmailSource::OBJ_TYPE_TICKET,
        ]);

        $this->getContainer()->getEm()->persist($source);
        $this->getContainer()->getEm()->flush();

        // TODO the source id should not replace the message id
        $dto->setMessageId($source->getId());

        /** @var AmazonS3Storage $s3Adapter */
        $s3Adapter = $blobStorage->getAdapter("s3");
        $location = new DTOMessageLocationS3();
        $location->setBucketName(
            $s3Adapter->getOption("bucket")
        );
        $location->setObjectKey(
            $s3Adapter->resolvePath($blob->getSavePath())
        );
        $dto->setLocation($location);

        return $dto;
    }

    /**
     * @Rest\Post("/{id}/process")
     * @param $request
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function processSource(Request $request, $id)
    {
        /** @var EmailSource $source */
        $source = $this->findOr404(EmailSource::class, $id);

        // read the raw source to refresh some fields on the email source and adjust the blob size
        $blob = $source->getBlob();

        $rawSource = $this->getContainer()->getBlobStorage()->downloadBlobData($blob);
        if (empty($rawSource)) {
            throw new \Exception("Unexpected empty email source. Maybe downloading failed");
        }
        $blob->setFilesize(strlen($rawSource));

        $accountManager = $this->getContainer()->getEmailAccountManager();
        $readerFactory = $this->getContainer()->getEmailEzcReaderFactory();

        $mapper = new PropertyMapper($accountManager, $readerFactory);
        $reader = $mapper->createReader($rawSource);
        $mapper->read($reader, $source);
        $source->fromArray([
            'status'         => EmailSource::STATUS_INSERTED,
            'object_type'    => EmailSource::OBJ_TYPE_TICKET,
        ]);

        $this->getContainer()->getEm()->persist($source);
        $this->getContainer()->getEm()->flush();

        // TODO investigate if the commented lines below are are actually needed
        // Set the copied raw source or else $source->getRawSource() will
        // attempt to load it from the blob storage which is wasteful (eg could read back from s3 what we just wrote)
        //$source->_raw = $rawSource;

        $runner = new Runner();
        $result = $runner->executeSource($source, $reader);

        return [ "status" => $source->getStatus(), "messageId" => $id ];
    }
}
