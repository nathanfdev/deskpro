<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter;

use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixture;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\FixtureDeleteInterface;
use Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper\HelpCenter\Category;
use DateTime;
use Zendesk\API\Client;
use Zendesk\API\ResponseException;

/**
 * Class Categories.
 */
final class Categories extends AbstractFixture implements FixtureDeleteInterface
{
    /**
     * @var CategoryLoader
     */
    private $category_loader;

    /**
     * Constructor.
     *
     * @param Client         $client
     * @param CategoryLoader $category_loader
     */
    public function __construct(Client $client, CategoryLoader $category_loader)
    {
        parent::__construct($client);
        $this->category_loader = $category_loader;
    }

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
    public function delete()
    {
        $helper = new Category($this->client);

        try {
            $categories = $this->category_loader->getFakeCategories();
            foreach ($categories as $category) {
                $helper->delete(['id' => $category['id']]);
                $this->logInfo(sprintf('Category `%s` deleted successfully', $category['name']));
            }
        } catch (ResponseException $e) {
            $this->handleResponseException('category');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create($offset, DateTime $initial_time, DateTime $end_time)
    {
        if (!$this->category_loader->hasPrimaryCategory()) {
            try {
                $this->logInfo('Importing primary category');

                $helper   = new Category($this->client);
                $response = $helper->create([
                    'category' => [
                        'name'        => 'Primary Category',
                        'description' => 'Primary Category description',
                    ],
                ]);

                $this->logInfo('Primary category imported successfully');
                $this->logger->debug(json_encode($response->category));
            } catch (ResponseException $e) {
                $this->handleResponseException($this->getEntityType());
            }
        }

        parent::create($offset, $initial_time, $end_time);
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($num, DateTime $initial_time, DateTime $end_time)
    {
        $helper   = new Category($this->client);
        $response = $helper->create([
            'category' => [
                'name'        => CategoryLoader::FAKE_PREFIX.' '.$num,
                'description' => CategoryLoader::FAKE_PREFIX.' description '.$num,
            ],
        ]);

        $this->logger->info('Category created successfully');
        $this->logger->debug(json_encode($response->category));
    }
}
