<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk\Helper;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserFormatterHelper;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception\BadResponseException;

/**
 * Class Attachment.
 */
class Attachment extends AbstractParserFormatterHelper
{
    /**
     * @var HttpClient
     */
    private $http_client;

    /**
     * Constructor.
     *
     * @param FormatterInterface $formatter
     * @param HttpClient         $http_client
     */
    public function __construct(FormatterInterface $formatter, HttpClient $http_client)
    {
        parent::__construct($formatter);
        $this->http_client = $http_client;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ATTACHMENT;
    }

    /**
     * Returns a collection of the ticket message attachments.
     *
     * @param array $attachments
     *
     * @return Entity\Attachment[]|Entity\Collection
     */
    public function export(array $attachments)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($attachments)
            ->setPrefix('ZDAttachment')
            ->setRefColumn('id')
            ->setMethod('exportAttachment')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an attachment entity.
     *
     * @param array $data
     *
     * @return Entity\Attachment|null
     */
    public function exportAttachment(array $data)
    {
        $formatted = $this->formatter->format($data, [
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'attachment_',
                'ref'    => 'id',
            ]),
            'file_name'    => TransformerInterface::TYPE_STRING,
            'content_type' => TransformerInterface::TYPE_STRING,
            'content_url'  => TransformerInterface::TYPE_STRING,
            'inline'       => TransformerInterface::TYPE_BOOLEAN,
        ]);

        if ($formatted['inline']) {
            throw new SkippingException('Inline attachment, skipping', $formatted);
        }

        try {
            $request   = $this->http_client->get($formatted['content_url']);
            $blob_data = base64_encode($request->send()->getBody(true));
        } catch (BadResponseException $e) {
            $this->logWarning($e->getMessage());

            $response = $e->getResponse();
            if ($response) {
                $this->logWarning(sprintf('Status code: %s', $response->getStatusCode()));
                $this->logWarning(sprintf('Reason phrase: %s', $response->getReasonPhrase()));
            }

            throw new SkippingException('Unable to download attachment', $formatted);
        }

        $entity = new Entity\Attachment();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setBlobData($blob_data)
            ->setFileName($formatted['file_name'])
            ->setContentType($formatted['content_type'])
        ;

        return $entity;
    }
}
