<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer\CustomFields;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer\AbstractDataSerializerTransformer;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;

/**
 * Class CustomDataCollectionTransformer.
 */
class CustomDataCollectionTransformer extends AbstractDataSerializerTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var CustomDataCollection $coll */
        $coll = $transformation_request->getDataToBeTransformed();

        $data = [];

        foreach ($coll->getData() as $cd) {
            $row = [];

            switch ($cd->getField()->getTypeName()) {
                case 'choice':
                    $vals    = [];
                    $details = [];

                    foreach ($cd->getData() as $v) {
                        $fid = $v->field->getId();// value is the ID of the selected option

                        $vals[]        = $fid;
                        $details[$fid] = [
                            'id'    => $v->field->getId(),
                            'title' => $v->field->getTitle(),
                        ];
                    }

                    $row['value']  = $vals;
                    $row['detail'] = $details;
                    break;

                case 'date':
                case 'datetime':
                    $v = $cd->getData();
                    $v = array_pop($v);

                    if ($v) {
                        $v = $v->getInput();

                        try {
                            $row['value'] = new \DateTime('@'.$v);
                        } catch (\Exception $e) {
                            try {
                                $row['value'] = new \DateTime($v);
                            } catch (\Exception $e) {
                                $row['value'] = null;
                            }
                        }
                    } else {
                        $row['value'] = null;
                    }
                    break;

                default:
                    $v = $cd->getData();
                    $v = array_pop($v);
                    if ($v) {
                        $row['value'] = $v->getData();
                    } else {
                        $row['value'] = null;
                    }
                    break;
            }

            $data[$cd->getField()->getId()] = $row;
        }

        return $data;
    }
}
