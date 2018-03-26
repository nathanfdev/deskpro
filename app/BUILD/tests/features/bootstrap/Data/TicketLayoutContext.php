<?php

namespace DpBehat\Data;

use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use DpBehat\BaseContext;

/**
 * Class TicketLayoutContext.
 */
class TicketLayoutContext extends BaseContext
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
     * @Given the default ticket layout exists with fields:
     *
     * @param TableNode $fields
     */
    public function theDefaultTicketLayoutExists(TableNode $fields)
    {
        $this->theTicketLayoutExists(null, $fields);
    }

    /**
     * @Given the only default ticket layout exists with fields:
     *
     * @param TableNode $fields
     */
    public function theOnlyDefaultTicketLayoutExists(TableNode $fields)
    {
        $this->dataContext->noRecordsExist('TicketLayout');
        $this->theTicketLayoutExists(null, $fields);
    }

    /**
     * @Given the ticket layout exists for :departmentRef department with fields:
     *
     * @param string    $departmentRef
     * @param TableNode $fields
     *
     * @throws \Exception
     */
    public function theTicketLayoutExists($departmentRef, TableNode $fields)
    {
        /* @var Layout[] $layouts */
        $layouts['user_layout']  = new Layout();
        $layouts['agent_layout'] = new Layout();

        foreach ($fields->getHash() as $data) {
            foreach (array_keys($layouts) as $context) {
                if (empty($data[$context])) {
                    continue;
                }

                $fieldType = DataContext::replace($data[$context]);
                $fieldId   = null;
                if (preg_match('/^(\w+)_(\d+)$/', $fieldType, $matches)) {
                    $fieldType = $matches[1];
                    $fieldId   = (int) $matches[2];
                }

                $layoutField = new LayoutField($fieldType, $fieldId);
                if (!empty($data[$context.'_options'])) {
                    $options = ObjectsManager::preProcessValue($data[$context.'_options']);
                    if (!is_array($options)) {
                        throw new \Exception("Unable to set layout options for the field $fieldType");
                    }

                    $layoutField->setOptionsFromArray($options);
                }

                $layouts[$context]->add($layoutField);
            }
        }

        $ticketLayout = new TicketLayout();
        $ticketLayout->setIsEnabled(true);
        $ticketLayout->setDepartment($departmentRef ? DataContext::resolveReference($departmentRef) : null);
        $ticketLayout->setUserLayout($layouts['user_layout']);
        $ticketLayout->setAgentLayout($layouts['agent_layout']);

        $this->persistAndFlush($ticketLayout);
    }
}
