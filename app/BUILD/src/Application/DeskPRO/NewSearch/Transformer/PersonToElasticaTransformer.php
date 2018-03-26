<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\Entity\Person;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

/**
 * Person To Elastica Transformer.
 */
class PersonToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param Person $object
     */
    public function transform($object, array $fields)
    {
        $document = new Document();
        $document->setId($object->getId());
        $document->set('name', $object->name);
        $document->set('first_name', $object->first_name);
        $document->set('last_name', $object->last_name);
        $document->set('is_agent', $object->isAgent());

        $emails       = [];
        $emailDomains = [];
        foreach ($object->getEmails() as $e) {
            $emails[] = $e->getEmail();
            if ($e->getEmailDomain()) {
                $emailDomains[] = $e->getEmailDomain();
            }
        }

        $document->set('emails', $emails);
        if ($emailDomains) {
            $document->set('email_domains', $emailDomains);
        }

        $phones = [];
        foreach ($object->phone_numbers as $p) {
            $pn = $p->getPhoneNumber();
            if ($pn) {
                $phones[] = '+'.$pn->getCountryCode().' '.preg_replace('#[^0-9]#', '', $pn->getNationalNumber());
            }
        }

        $document->set('phone_numbers', $phones);

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
