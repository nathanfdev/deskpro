<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
                    $customData[] = $value->getInput();
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
