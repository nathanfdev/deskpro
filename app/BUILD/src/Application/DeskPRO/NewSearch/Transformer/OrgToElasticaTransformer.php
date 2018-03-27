<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\Entity\Organization;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

/**
 * Person To Elastica Transformer.
 */
class OrgToElasticaTransformer implements ModelToElasticaTransformerInterface
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

        $document->set('name', $object->name);

        $email_domains = [];
        foreach ($object->email_domains as $d) {
            $email_domains[] = $d->domain;
        }

        if ($email_domains) {
            $document->set('email_domains', $email_domains);
        }

        if ($object->labels) {
            $labels = Arrays::map(function ($l) {
                return $l->label;
            }, $object->labels);
            $document->set('labels', array_values($labels));
        }

        $document->set('date_created', $object->date_created->format('Y-m-d H:i:s'));
        $document->set('date_active', date('Y-m-d H:i:s'));

        return $document;
    }
}
