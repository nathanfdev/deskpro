<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Elastica\Document;
use Application\DeskPRO\Entity\Organization;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

/**
 * Person To Elastica Transformer
 */
class OrgToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * Transform
     *
     * @param Organization $object
     * @param array $fields
     *
     * @return Document
     */
    public function transform($object, array $fields)
    {
        $document = new Document();

        $document->setId($object->getId());

        $document->set('name', $object->name);

		$email_domains = array();
		foreach ($object->email_domains as $d) {
			$email_domains[] = $d->domain;
		}

		if ($email_domains) {
			$document->set('email_domains', $email_domains);
		}

		if ($object->labels) {
			$labels = Arrays::map(function ($l) { return $l->label; }, $object->labels);
			$document->set('labels', $labels);
		}

        return $document;
    }
} 