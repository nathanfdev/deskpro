<?php

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
