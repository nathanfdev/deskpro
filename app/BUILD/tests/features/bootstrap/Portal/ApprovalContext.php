<?php

namespace DpBehat\Portal;

use Behat\Gherkin\Node\TableNode;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Entity\Approval\SelectedApprovers;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ApprovalContext
 *
 * @package DpBehat\Portal
 */
class ApprovalContext extends BasePortalContext
{
    /**
     * @var ApprovalType[]
     */
    public static $approvalTypes = [];

    /**
     * @var ApprovalTemplate[]
     */
    public static $approvalTemplates = [];

    /**
     * @var TicketApproval[]
     */
    public static $ticketApprovals = [];

    /**
     * @Given the following approval types exist:
     */
    public function theFollowingApprovalTypesExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $type = (new ApprovalType())
                ->setName($row['name'])
                ->setDescription($row['description'])
                ->setIsDeleted((bool) $row['isDeleted'])
            ;

            $this->em()->persist($type);
            $this->em()->flush();

            self::$approvalTypes[$row['ref']] = $type;
        }
    }

    /**
     * @Given the following approval templates exist:
     */
    public function theFollowingApprovalTemplatesExist(TableNode $table)
    {
        $agent = $this->getWho('agent');
        $user = $this->getWho('user');

        foreach ($table->getHash() as $row) {
            $selection = (new SelectedApprovers())
                ->setPeople([$agent->getId(), $user->getId()])
            ;

            $template = (new ApprovalTemplate())
                ->setCanChooseApprovers(false)
                ->setSelectedApprovers($selection)
                ->setType(self::$approvalTypes[$row['type']])
                ->setName($row['name'])
                ->setDescription($row['description'])
                ->setCanApproversViewSubject((bool) $row['canApproversViewSubject'])
                ->setRequiredApprovals(2)
                ->setRequiredRejections(2)
            ;

            $this->em()->persist($template);
            $this->em()->flush();

            self::$approvalTemplates[$row['ref']] = $template;
        }
    }

    /**
     * @Given the following ticket approvals exist:
     */
    public function theFollowingTicketApprovalsExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $approval = TicketApproval::createTicketApprovalFromTemplate(
                self::$tickets[$row['ticket']],
                $this->em(),
                self::$approvalTemplates[$row['template']]
            );

            $approval
                ->setDescription($row['description'])
            ;

            $this->em()->persist($approval);
            $this->em()->flush();

            self::$ticketApprovals[$row['ref']] = $approval;
        }
    }

    /**
     * @Given the ticket approvals table must contain :numberOfRows row(s)
     */
    public function theTicketApprovalsTableMustContainNRows($numberOfRows)
    {
        $crawler = new Crawler($this->getSession()->getPage()->getHtml());
        $rowCount = $crawler->filter('.user-approval-list tbody tr.item-row')->count();

        expect($rowCount)->toBe((int) $numberOfRows);
    }

    /**
     * @Given row :rowNum of the ticket approvals table must contain the :columnName :content
     */
    public function rowNofTheTicketApprovalsTableMustContainTheColumnContent($rowNum, $columnName, $content)
    {
        $crawler = new Crawler($this->getSession()->getPage()->getHtml());

        $colRef = $crawler
            ->filter(sprintf('.user-approval-list thead tr td:contains("%s")', $columnName))
            ->attr('data-col')
        ;

        $cell = $crawler
            ->filter(sprintf('.user-approval-list tbody tr.item-row:nth-child(%d) td[data-col=%s]', $rowNum, $colRef))
        ;

        expect(trim($cell->text()))->toBe($content);
    }

    /**
     * @Given approvals tables are cleared
     */
    public function approvalsTablesAreCleared()
    {
        $conn = $this->em()->getConnection();

        $conn->executeUpdate('DELETE FROM approval_responses');
        $conn->executeUpdate('DELETE FROM approvals');
        $conn->executeUpdate('DELETE FROM approval_templates');
        $conn->executeUpdate('DELETE FROM approval_types');
    }

    /**
     * @Given I view ticket approval :ref
     */
    public function viewTicketApprovalX($ref)
    {
        $this->visitPath('/approvals/'.self::$ticketApprovals[$ref]->getId());
    }
}
