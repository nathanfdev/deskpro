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

namespace Application\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity as DeskPROEntity;

/**
 * Abstract custom def mapper.
 *
 * Class AbstractCustomDefMapper
 */
abstract class AbstractCustomDefMapper extends AbstractImportMapMapper
{
    /**
     * Find a choice custom def entity
     * We store value for choice custom fields like "A > A1".
     *
     * MyField
     *   Option A
     *     |- Option A1
     *     |- Option A2
     *   Option B
     *     |- Option B1
     *     |- Option B2
     *
     * @param array|string                    $choiceChain
     * @param DeskPROEntity\CustomDefAbstract $customDef
     *
     * @return DeskPROEntity\CustomDefAbstract|false
     */
    public function findChoiceCustomDef($choiceChain, DeskPROEntity\CustomDefAbstract $customDef)
    {
        if (!$customDef->isChoiceType()) {
            return false;
        }

        if (is_string($choiceChain)) {
            $choiceChain = explode('>', $choiceChain);
            $choiceChain = array_map('trim', $choiceChain);
        }

        $iterator = function (array $choiceChain, $parentId) use ($customDef, &$iterator) {
            $title = array_shift($choiceChain);
            foreach ($customDef->getChildren() as $choiceDef) {
                if ($choiceDef->getTitle() === $title && $parentId === (int) $choiceDef->getOption('parent_id', 0)) {
                    if (count($choiceChain)) {
                        return $iterator($choiceChain, $choiceDef->getId());
                    } else {
                        return $choiceDef;
                    }
                }
            }

            return false;
        };

        return $iterator($choiceChain, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        $record = null;
        $id     = $this->findImportMapNewId($criteria);

        if ($id) {
            $record = $this->em->getRepository($this->getEntityClass())->find($id);
        } else {
            if (isset($criteria['entity'])) {
                unset($criteria['entity']);
            }

            if (!empty($criteria)) {
                $record = $this->em->getRepository($this->getEntityClass())->findOneBy($criteria);
            }
        }

        /** @var DeskPROEntity\CustomDefAbstract $record */
        if (!$record && $throw_exception) {
            throw new MapperException(sprintf('Custom def `%s` not found', $this->getEntityClass()), $criteria);
        }

        return $record;
    }
}
