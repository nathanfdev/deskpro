<?php

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
