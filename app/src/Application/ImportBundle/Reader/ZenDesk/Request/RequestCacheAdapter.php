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

/**
 * Cache wrapper of ZenDesk API request adapter
 *
 * Class RequestCacheAdapter
 * @package Application\ImportBundle\Reader\ZenDesk\Request
 */
final class RequestCacheAdapter implements RequestAdapterInterface
{
    /**
     * @var RequestAdapterInterface
     */
    private $request_adapter;

    /**
     * @var array
     */
    private $cache = array();

    /**
     * Constructor
     *
     * @param RequestAdapterInterface $request_adapter
     */
    public function __construct(RequestAdapterInterface $request_adapter)
    {
        $this->request_adapter = $request_adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function doPeopleIncrementalExportRequest(array $params = array())
    {
        return $this->doRequest(__FUNCTION__, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function doPeopleFindRequest(array $params = array())
    {
        return $this->doRequest(__FUNCTION__, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function doOrganizationFindRequest(array $params = array())
    {
        return $this->doRequest(__FUNCTION__, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function doTicketsIncrementalExportRequest(array $params = array())
    {
        return $this->doRequest(__FUNCTION__, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function doTicketCommentsFindAllRequest(array $params = array())
    {
        return $this->doRequest(__FUNCTION__, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function doArticleIncrementalExportRequest(array $params = array())
    {
        return $this->doRequest(__FUNCTION__, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function doArticleCommentsFindAllRequest(array $params = array())
    {
        return $this->doRequest(__FUNCTION__, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function doArticleAttachmentsFindAllRequest(array $params = array())
    {
        return $this->doRequest(__FUNCTION__, $params);
    }

    /**
     * Do request to ZenDesk API or get from cache
     *
     * @param string $method
     * @param array  $params
     *
     * @return \stdClass
     */
    private function doRequest($method, array $params)
    {
        $hash = md5($method . json_encode($params));
        if ( ! isset($this->cache[$hash])) {
            $this->cache[$hash] = $this->request_adapter->$method($params);
        }

        return $this->cache[$hash];
    }
}
