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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;

/**
 * Class ArticleCategories
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
 */
final class ArticleCategories extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE_CATEGORY;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $categories = $this->reader->getArticlesCategories();
        $sections   = $this->reader->getArticlesSections();

        return count($categories) + count($sections);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = $this->exportCategories();
        $sections   = $this->exportSections();

        foreach ($collection as $category) {
            foreach ($sections as $section) {
                if ($section->getDestination() == $category->getDestination()) {
                    $category->addCategory($section);
                }
            }
        }

        return $collection;
    }

    /**
     * @return Entity\Collection|Entity\ArticleCategory[]
     */
    private function exportCategories()
    {
        $collection = new Entity\Collection();
        $collection->setExpectedCount($this->getCount());

        $categories = $this->reader->getArticlesCategories();

        foreach ($categories as $data) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportCategory($data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (SkippingException $e) {
                $this->logSkippingException('ZDArticleCategory', $this->getEntityType(), 'id', $e);
            } catch (TransformerException $e) {
                $this->logTransformerException('ZDArticleCategory', $this->getEntityType(), 'id', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('ZDArticleCategory', $this->getEntityType(), 'id', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * @param array $data
     * @return Entity\ArticleCategory
     */
    private function exportCategory(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'           => TransformerInterface::TYPE_INT,
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_category_',
                'ref'    => 'id',
            )),
            'name'         => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\ArticleCategory();
        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['name'])
        ;

        return $entity;
    }

    /**
     * @return Entity\Collection|Entity\ArticleCategory[]
     */
    private function exportSections()
    {
        $collection = new Entity\Collection();
        $sections   = $this->reader->getArticlesSections();

        foreach ($sections as $data) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportSection($data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (SkippingException $e) {
                $this->logSkippingException('ZDArticleSection', $this->getEntityType(), 'id', $e);
            } catch (TransformerException $e) {
                $this->logTransformerException('ZDArticleSection', $this->getEntityType(), 'id', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('ZDArticleSection', $this->getEntityType(), 'id', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * @param array $data
     * @return Entity\ArticleCategory
     */
    private function exportSection(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'           => TransformerInterface::TYPE_INT,
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_category_',
                'ref'    => 'category_id',
            )),
            'name'         => TransformerInterface::TYPE_STRING,
            'category_id'  => TransformerInterface::TYPE_INT,
        ));

        $entity = new Entity\ArticleCategory();
        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['name'])
        ;

        return $entity;
    }
}
