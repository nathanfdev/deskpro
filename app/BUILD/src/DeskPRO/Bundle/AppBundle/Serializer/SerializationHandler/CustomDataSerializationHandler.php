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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Serializer\SerializationHandler;

use Application\DeskPRO\Entity\CustomDataAbstract;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer\CustomFields\CustomDataCollection;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class CustomDataSerializationHandler.
 */
class CustomDataSerializationHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => SerializerTypes::TYPE_CUSTOM_DATA,
                'method'    => 'serializeCollection',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param CustomDataAbstract[]         $collection
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    public function serializeCollection(JsonSerializationVisitor $visitor, $collection, $type, SideloadSerializationContext $context)
    {
        $collection = new CustomDataCollection($collection);
        $data       = [];

        foreach ($collection->getData() as $cd) {
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

            $data[$cd->getField()->getId()] = $context->accept($row);
        }

        return $data;
    }
}
