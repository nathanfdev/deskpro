<?php

namespace Application\DeskPRO\NewSearch\Transformer;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Labels\LabelsOwner;
use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Orb\Util\Arrays;

/**
 * Class AbstractToElasticaTransformer.
 */
abstract class AbstractToElasticaTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * @param mixed    $object
     * @param Document $document
     */
    protected function transformCustomData($object, Document $document)
    {
        $customData = [];
        if ($object->getCustomData()) {
            /** @var CustomDataAbstract $value */
            foreach ($object->getCustomData() as $value) {
                if (in_array($value->getRootField()->getType(), [CustomDefAbstract::TYPE_TEXT, CustomDefAbstract::TYPE_TEXTAREA])) {
                    $customData[] = ['value' => $value->getData(), 'id' => $value->getFieldId()];
                }
            }
        }

        $document->set('custom_data', $customData);
    }

    /**
     * @param LabelsOwner $object
     * @param Document    $document
     */
    protected function transformLabels(LabelsOwner $object, Document $document)
    {
        if ($object->getLabels()) {
            $labels = Arrays::map(function ($l) {
                return $l->label;
            }, $object->getLabels());
            $document->set('labels', array_values($labels));
        } else {
            $document->set('labels', []);
        }
    }
}
