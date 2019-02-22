<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\Entity\Person;
use Elastica\Document;

/**
 * Person To Elastica Transformer.
 */
class PersonToElasticaTransformer extends AbstractToElasticaTransformer
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
        $document->set('name', $object->getName());
        $document->set('first_name', $object->getFirstName());
        $document->set('last_name', $object->getLastName());
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
        foreach ($object->getPhoneNumbers() as $p) {
            $pn = $p->getPhoneNumber();
            if ($pn) {
                $phones[] = '+'.$pn->getCountryCode().' '.preg_replace('#[^0-9]#', '', $pn->getNationalNumber());
            }
        }

        $document->set('phone_numbers', $phones);
        $document->set('date_created', $object->getDateCreated()->format('Y-m-d H:i:s'));
        $document->set('date_active', date('Y-m-d H:i:s'));

        $this->transformCustomData($object, $document);
        $this->transformLabels($object, $document);

        return $document;
    }
}
