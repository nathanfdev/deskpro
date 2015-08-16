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

use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixture;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\FixturePrepareInterface;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\CategoriesFindAll;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\SectionCreate;
use DateTime;
use Zendesk\API\ResponseException;

/**
 * Class Sections
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter
 */
final class Sections extends AbstractFixture implements FixturePrepareInterface
{
    /**
     * @var array
     */
    private $categories = array();

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return 'section';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(DateTime $initial_time, DateTime $end_time)
    {
        $helper = new CategoriesFindAll();

        try {
            $this->categories = $helper->request($this->client)->categories;

        } catch (ResponseException $e) {
            $this->handleResponseException();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($prefix, DateTime $initial_time, DateTime $end_time)
    {
        if (empty($this->categories)) {
            throw new \RuntimeException('No help center category');
        }

        $category = $this->categories[rand(0, count($this->categories) - 1)];
        $helper   = new SectionCreate(array(
            'category_id' => $category->id,
            'section'     => array(
                'name' => $category->name . ': Section' . $prefix,
            ),
        ));

        $helper->request($this->client);
    }
}
