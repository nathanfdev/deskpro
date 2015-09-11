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
        $this->addResponse('CoreAPI\PeopleIncrementalExport', $response);
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
        $this->addResponse('CoreAPI\PeopleFind', $response);
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
        $this->addResponse('CoreAPI\OrganizationFind', $response);
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
        $this->addResponse('CoreAPI\TicketsIncrementalExport', $response);
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
        $this->addResponse('CoreAPI\TicketCommentsFindAll', $response);
        return $this;
    }

    /**
     * Stores an article incremental export response
     *
     * @param string|array $response
     * @return $this
     */
    public function addArticlesIncrementalExportResponse($response)
    {
        $this->addResponse('HelpCenter\ArticleIncrementalExport', $response);
        return $this;
    }

    /**
     * Stores an article comments response
     *
     * @param string|array $response
     * @return $this
     */
    public function addArticleCommentsFindAllResponse($response)
    {
        $this->addResponse('HelpCenter\ArticleCommentsFindAll', $response);
        return $this;
    }

    /**
     * Stores an article attachments response
     *
     * @param string|array $response
     * @return $this
     */
    public function addArticleAttachmentsFindAllResponse($response)
    {
        $this->addResponse('HelpCenter\ArticleAttachmentsFindAll', $response);
        return $this;
    }

    /**
     * Stores an article translations response
     *
     * @param string|array$response
     * @return $this
     */
    public function addArticleTranslationsFindAllResponse($response)
    {
        $this->addResponse('HelpCenter\ArticleTranslationsFindAll', $response);
        return $this;
    }

    /**
     * Stores an article categories response
     *
     * @param string|array $response
     * @return $this
     */
    public function addArticleCategoriesFindAll($response)
    {
        $this->addResponse('HelpCenter\CategoriesFindAll', $response);
        return $this;
    }

    /**
     * Stores an article sections response
     *
     * @param string|array $response
     * @return $this
     */
    public function addArticleSectionsFindAll($response)
    {
        $this->addResponse('HelpCenter\SectionsFindAll', $response);
        return $this;
    }

    /**
     * Stores an article category response
     *
     * @param string|array $response
     * @return $this
     */
    public function addArticleSectionFindResponse($response)
    {
        $this->addResponse('HelpCenter\SectionFind', $response);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function doRequest($helper_class, array $params = array())
    {
        if (empty($this->responses[$helper_class])) {
            throw new RuntimeException(sprintf('No response exists for `%s`', $helper_class));
        }

        return array_shift($this->responses[$helper_class]);
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
}
