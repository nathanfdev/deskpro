<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\Entity\Organization;
use Elastica\Document;

/**
 * Person To Elastica Transformer.
 */
class OrgToElasticaTransformer extends AbstractToElasticaTransformer
{
    /**
     * {@inheritdoc}
     *
     * @param Organization $object
     */
    public function transform($object, array $fields)
    {
        $document = new Document();
        $document->setId($object->getId());
        $document->set('name', $object->getName());

        $email_domains = [];
        foreach ($object->getEmailDomains() as $d) {
            $email_domains[] = $d->domain;
        }

        if ($email_domains) {
            $document->set('email_domains', $email_domains);
        }

        $document->set('date_created', $object->getDateCreated()->format('Y-m-d H:i:s'));
        $document->set('date_active', date('Y-m-d H:i:s'));

        $this->transformCustomData($object, $document);
        $this->transformLabels($object, $document);

        return $document;
    }
}
