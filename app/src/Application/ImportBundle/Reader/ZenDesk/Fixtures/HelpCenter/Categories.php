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
use Application\ImportBundle\Reader\ZenDesk\Fixtures\FixtureDeleteInterface;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\CategoriesFindAll;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\CategoryCreate;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\CategoryDelete;
use DateTime;
use Zendesk\API\ResponseException;

/**
 * Class Categories
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter
 */
final class Categories extends AbstractFixture implements FixtureDeleteInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function delete($offset)
    {
        try {
            $helper = new CategoriesFindAll();
            $categories = $helper->request($this->client);

            $min = $offset;
            $max = $offset + self::COUNT;

            foreach ($categories->categories as $category) {
                $prefix = str_replace('Category', '', $category->name);

                if ($min <= $prefix && $prefix <= $max) {
                    $helper = new CategoryDelete(array('id' => $category->id));
                    $helper->request($this->client);
                }
            }

        } catch (ResponseException $e) {
            $this->handleResponseException('category');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($prefix, DateTime $initial_time, DateTime $end_time)
    {
        $helper = new CategoryCreate(array(
            'category' => array(
                'name'        => 'Category' . $prefix,
                'description' => 'Category description' . $prefix,
            ),
        ));

        $response = $helper->request($this->client);

        $this->logger->info('Category created successfully');
        $this->logger->debug(json_encode($response->category));
    }
}
