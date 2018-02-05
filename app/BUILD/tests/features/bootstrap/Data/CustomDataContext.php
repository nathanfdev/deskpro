<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpBehat\Data;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Ticket;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use DpBehat\BaseContext;

/**
 * Class CustomDataContext.
 */
class CustomDataContext extends BaseContext
{
    /**
     * @var DataContext
     */
    private $dataContext;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->dataContext = $scope->getEnvironment()->getContext('DpBehat\Data\DataContext');
    }

    /**
     * @Given the object :entityRef has :customDefRef custom data set to :value
     *
     * @param string $entityRef
     * @param string $customDefRef
     * @param string $value
     *
     * @throws \Exception
     */
    public function theObjectHasCustomDataWith($entityRef, $customDefRef, $value)
    {
        /** @var CustomDefAbstract $customDef */
        $customDef = $this->dataContext->getReference($customDefRef);
        $entity    = $this->dataContext->getReference($entityRef);
        $value     = DataContext::replace($value);

        if (!method_exists($entity, 'getCustomData') || !method_exists($entity, 'addCustomData')) {
            throw new \Exception("$entityRef doesn't support custom data");
        }
        if ($entity instanceof Ticket) {
            $entity->disableAutoTicketProcess();
        }

        if ($customDef->isChoiceType()) {
            foreach (explode(',', $value) as $choiceId) {
                $choiceDef = $customDef->getChildById($choiceId);
                if (!$choiceDef) {
                    throw new \Exception("$customDefRef doesn't have choice $choiceId");
                }

                $customData = $customDef->createCustomData();
                $customData->setField($choiceDef);
                $customData->setValue(1);

                $entity->addCustomData($customData);
            }
        } else {
            $customData = $customDef->createCustomData();

            if ($customDef->getWidgetType() === CustomDefAbstract::TYPE_TOGGLE) {
                $customData->setValue($value);
            } elseif ($customDef->isDateType()) {
                $date = new \DateTime($value);
                if ($customDef->getWidgetType() === CustomDefAbstract::TYPE_DATE) {
                    $date->modify('midnight');
                }

                $customData->setValue($date->getTimestamp());
            } else {
                $customData->setData($value);
            }

            $entity->addCustomData($customData);
        }

        $this->persistAndFlush($entity);
    }

    /**
     * @Given there are no custom ticket fields defined
     */
    public function noCustomTicketFieldsExist()
    {
        $this->dataContext->noRecordsExist('CustomDefTicket');
    }

    /**
     * @Given the following custom ticket fields exist:
     *
     * @param TableNode $table
     */
    public function theFollowingCustomTicketFieldsExist(TableNode $table)
    {
        $this->dataContext->theFollowingRecordsExist('CustomDefTicket', $table);
    }

    /**
     * @Given only the following custom ticket fields exist:
     *
     * @param TableNode $table
     */
    public function onlyTheFollowingCustomTicketFieldsExist(TableNode $table)
    {
        $this->dataContext->onlyTheFollowingRecordsExist('CustomDefTicket', $table);
    }

    /**
     * @Given only the following custom organization fields exist:
     *
     * @param TableNode $table
     */
    public function onlyTheFollowingCustomOrganizationFieldsExist(TableNode $table)
    {
        $this->dataContext->onlyTheFollowingRecordsExist('CustomDefOrganization', $table);
    }

    /**
     * @Given only the following custom feedback fields exist:
     *
     * @param TableNode $table
     */
    public function onlyTheFollowingCustomFeedbackFieldsExist(TableNode $table)
    {
        $this->dataContext->onlyTheFollowingRecordsExist('CustomDefFeedback', $table);
    }

    /**
     * @Given only the following custom person fields exist:
     *
     * @param TableNode $table
     */
    public function onlyTheFollowingCustomPersonFieldsExist(TableNode $table)
    {
        $this->dataContext->onlyTheFollowingRecordsExist('CustomDefPerson', $table);
    }

    /**
     * @Given only the following custom chat fields exist:
     *
     * @param TableNode $table
     */
    public function onlyTheFollowingCustomChatFieldsExist(TableNode $table)
    {
        $this->dataContext->onlyTheFollowingRecordsExist('CustomDefChat', $table);
    }

    /**
     * @Given only the following custom per user fields exist:
     *
     * @param TableNode $table
     */
    public function onlyTheFollowingCustomPerUserFieldsExist(TableNode $table)
    {
        $this->dataContext->onlyTheFollowingRecordsExist('CustomPerUserDef', $table);
    }

    /**
     * @Given only the following custom per organization fields exist:
     *
     * @param TableNode $table
     */
    public function onlyTheFollowingCustomPerOrganizationFieldsExist(TableNode $table)
    {
        $this->dataContext->onlyTheFollowingRecordsExist('CustomPerOrgDef', $table);
    }
}
