<?php

namespace DpBehat\Portal;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ApprovalContext
 */
class ApprovalContext extends BasePortalContext
{
    /**
     * @Given the ticket approvals table must contain :numberOfRows row(s)
     *
     * @param int $numberOfRows
     */
    public function theTicketApprovalsTableMustContainNRows($numberOfRows)
    {
        $crawler  = new Crawler($this->getSession()->getPage()->getHtml());
        $rowCount = $crawler->filter('.user-approval-list tbody tr.item-row')->count();

        expect($rowCount)->toBe((int) $numberOfRows);
    }

    /**
     * @Given row :rowNum of the ticket approvals table must contain the :columnName :content
     *
     * @param int    $rowNum
     * @param string $columnName
     * @param string $content
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
}
