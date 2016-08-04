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

namespace Application\DeskPRO\Form\Transformer\CustomFields;

use Application\DeskPRO\CustomFields\CustomDataPersister;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use DeskPRO\Bundle\AppBundle\Entity\CustomPerDataOwnerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class ChoiceDataTransformer.
 */
class ChoiceDataTransformer  implements DataTransformerInterface
{
    /**
     * @var CustomDataPersister
     */
    protected $persister;

    /**
     * @var DomainObject
     */
    protected $owner;

    /**
     * @var CustomFieldDefinition[]
     */
    protected $previous;

    /**
     * Constructor.
     *
     * @param CustomDataPersister         $persister
     * @param CustomPerDataOwnerInterface $owner
     */
    public function __construct(CustomDataPersister $persister, CustomPerDataOwnerInterface $owner)
    {
        $this->persister = $persister;
        $this->owner     = $owner;
    }

    /**
     * CustomFieldData to CustomFieldDefinition, CustomFieldData[] to CustomFieldDefinition[].
     *
     * @param mixed $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     *
     * @return array|mixed
     */
    public function transform($value)
    {
        $this->previous = array();

        if (!$value) {
            return array('value' => null);
        }

        // single choice
        if ($value instanceof CustomFieldData) {
            $this->previous[$value->definition['id']] = $value;

            return array('value' => $value->definition);
        }

        // multiple choices
        if (is_array($value)) {
            $coll = new ArrayCollection();
            foreach ($value as $data) {
                $this->previous[$data->definition['id']] = $data;
                $coll->add($data->definition);
            }

            return array('value' => $coll);
        }

        throw new TransformationFailedException();
    }

    /**
     * CustomFieldDefinition to CustomFieldData, CustomFieldDefinition[] to CustomFieldData[]
     * persisting/removing entities with EntityManager (flush is required somewhere outside).
     *
     * @param mixed $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     *
     * @return CustomFieldData|ArrayCollection|mixed|null
     */
    public function reverseTransform($value)
    {
        $value = $value['value'];
        if (!$value) {
            $this->persister->removeArray($this->previous);

            return;
        }

        // single choice
        if ($value instanceof CustomFieldDefinition) {
            $data = null;
            foreach ($this->previous as $previous) {
                /* @var $previous CustomFieldData */
                // mark to delete
                if ($previous->definition['id'] != $value['id']) {
                    $this->persister->remove($previous);
                } else {
                    $data = $previous;
                }
            }

            if (null === $data) {
                $data = $this->createNewData($value);
            }

            return $data;
        }

        // multiple choices
        if ($value instanceof ArrayCollection || is_array($value)) {
            $ret = [];

            foreach ($value as $definition) {
                /* @var $definition CustomFieldDefinition */
                if (!isset($this->previous[$definition['id']])) {
                    $ret[] = $this->createNewData($definition);
                } else {
                    $ret[] = $this->previous[$definition['id']];
                }
                unset($this->previous[$definition['id']]);
            }

            $this->persister->removeArray($this->previous);

            return $ret;
        }

        throw new TransformationFailedException();
    }

    /**
     * @param CustomFieldDefinition $definition
     *
     * @return CustomFieldData
     */
    protected function createNewData(CustomFieldDefinition $definition)
    {
        $data                  = new CustomFieldData();
        $data['value']         = 1;
        $data->definition      = $definition;
        $data->root_definition = $definition->parent ?: $definition;
        $data->owner           = $this->owner;
        $this->owner->getCustomPerData()->add($data);

        return $data;
    }
}
