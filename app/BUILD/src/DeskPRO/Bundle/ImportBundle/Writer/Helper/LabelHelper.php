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

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Labels\Label;
use Application\DeskPRO\Entity\Labels\LabelsOwner;
use DeskPRO\Bundle\ImportBundle\Model\LabelAwareModelInterface;
use Psr\Log\LoggerInterface;

/**
 * Class LabelHelper.
 */
class LabelHelper
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param LabelAwareModelInterface $model
     * @param LabelsOwner              $entity
     * @param string                   $labelClassName
     */
    public function updateLabels(LabelAwareModelInterface $model, LabelsOwner $entity, $labelClassName)
    {
        // add new labels
        foreach ($model->getLabels() as $labelName) {
            $filtered = $entity->getLabels()->filter(function (Label $label) use ($labelName) {
                return $label->getLabel() === $labelName;
            });

            if ($filtered->count()) {
                $this->logger->debug("Found existing label '$labelName', skipping'");
            } else {
                /** @var Label $label */
                $label = new $labelClassName();
                $label->setLabel($labelName);

                $entity->addLabel($label);
                $this->logger->debug("Add a new label '$labelName'");
            }
        }

        // remove deleted labels
        foreach ($entity->getLabels() as $label) {
            if (!in_array($label->getLabel(), $model->getLabels())) {
                $entity->removeLabel($label);
                $this->logger->debug("Remove label '{$label->getLabel()}'");
            }
        }
    }
}
