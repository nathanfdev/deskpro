<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Reader\ZenDesk\Request;

use RuntimeException;

/**
 * Class JsonMockAdapter
 * @package Application\ImportBundle\Reader\ZenDesk\Request
 */
class JsonMockAdapter implements RequestAdapterInterface
{
    /**
     * @var array
     */
    private $responses = array();

    /**
     * Stores a people incremental export response
     *
     * @param string|array $response
     * @return $this
     */
    public function addPeopleIncrementalExportResponse($response)
    {
        $this->addResponse('people_incremental_export', $response);
        return $this;
    }

    /**
     * Stores a people find response
     *
     * @param string|array $response
     * @return $this
     */
    public function addPeopleFindResponse($response)
    {
        $this->addResponse('people_find', $response);
        return $this;
    }

    /**
     * Stores a organization find response
     *
     * @param string|array $response
     * @return $this
     */
    public function addOrganizationFindResponse($response)
    {
        $this->addResponse('organization_find', $response);
        return $this;
    }

    /**
     * Stores a ticket incremental export response
     *
     * @param string|array $response
     * @return $this
     */
    public function addTicketsIncrementalExportResponse($response)
    {
        $this->addResponse('tickets_incremental_export', $response);
        return $this;
    }

    /**
     * Stores a ticket comments response
     *
     * @param string|array $response
     * @return $this
     */
    public function addTicketCommentsFindAllResponse($response)
    {
        $this->addResponse('ticket_comments_find_all', $response);
        return $this;
    }

    /**
     * Stores a article incremental export response
     *
     * @param string|array $response
     * @return $this
     */
    public function addArticlesIncrementalExportResponse($response)
    {
        $this->addResponse('articles_incremental_export', $response);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function doPeopleIncrementalExportRequest(array $params = array())
    {
        return $this->getResponse('people_incremental_export');
    }

    /**
     * {@inheritdoc}
     */
    public function doPeopleFindRequest(array $params = array())
    {
        return $this->getResponse('people_find');
    }

    /**
     * {@inheritdoc}
     */
    public function doOrganizationFindRequest(array $params = array())
    {
        return $this->getResponse('organization_find');
    }

    /**
     * {@inheritdoc}
     */
    public function doTicketsIncrementalExportRequest(array $params = array())
    {
        return $this->getResponse('tickets_incremental_export');
    }

    /**
     * {@inheritdoc}
     */
    public function doTicketCommentsFindAllRequest(array $params = array())
    {
        return $this->getResponse('ticket_comments_find_all');
    }

    /**
     * {@inheritdoc}
     */
    public function doArticleIncrementalExportRequest(array $params = array())
    {
        return $this->getResponse('articles_incremental_export');
    }

    /**
     * Stores a response
     *
     * @param string       $type
     * @param string|array $response
     */
    private function addResponse($type, $response)
    {
        if ( ! isset($this->responses[$type])) {
            $this->responses[$type] = array();
        }

        if (is_string($response)) {
            $content  = @file_get_contents($response);
            $response = @json_decode($content);
        }
        if ( ! is_object($response)) {
            throw new RuntimeException(sprintf('Bad response for `%s`', $type));
        }

        $this->responses[$type][] = $response;
    }

    /**
     * Returns a response by request
     *
     * @param string $type
     *
     * @return array
     * @throws RuntimeException
     */
    private function getResponse($type)
    {
        if (empty($this->responses[$type])) {
            throw new RuntimeException(sprintf('No response exists for `%s`', $type));
        }

        return array_shift($this->responses[$type]);
    }
}
