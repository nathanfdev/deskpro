<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Elastica\Document;
use Application\DeskPRO\Entity\Person;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;

/**
 * Person To Elastica Transformer
 */
class PersonToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * Transform
     *
     * @param Person $object
     * @param array $fields
     *
     * @return Document
     */
    public function transform($object, array $fields)
    {
        $document = new Document();

        $document->setId($object->getId());

        $document->set('name', $object->name);
        $document->set('first_name', $object->first_name);
        $document->set('last_name', $object->last_name);

		$emails = array();
		foreach ($object->emails as $e) {
			$emails[] = $e->email;
		}

		$document->set('emails', $emails);

		$phones = array();
		foreach ($object->phone_numbers as $p) {
			$pn = $p->getPhoneNumber();
			if ($pn) {
				$phones[] = "+" . $pn->getCountryCode() . " " . preg_replace('#[^0-9]#', '', $pn->getNationalNumber());
			}
		}

        $document->set('phone_numbers', $phones);

        return $document;
    }
} 