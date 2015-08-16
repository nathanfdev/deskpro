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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixture;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\FixturePrepareInterface;
use DateTime;
use Zendesk\API\Http;
use Zendesk\API\ResponseException;

/**
 * ZenDesk article fixtures
 *
 * Class Articles
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter
 */
final class Articles extends AbstractFixture implements FixturePrepareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(DateTime $initial_time, DateTime $end_time)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($prefix, DateTime $initial_time, DateTime $end_time)
    {
        $params = array(
            'title'      => 'Fake article ' . $prefix,
            'body'       => 'Fake article content',
            'author_id'  => '',
            'section_id' => '',
            'created_at' => '',
            'updated_at' => '',
        );

        $request_url = sprintf('incremental/articles.json?start_time=%s', $params['start_time']);
        $end_point   = Http::prepare($request_url);
        $response    = Http::send($this->client, $end_point);

        if (( ! is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
            throw new ResponseException(__METHOD__);
        }

        $this->client->setSideload(null);
    }
}
