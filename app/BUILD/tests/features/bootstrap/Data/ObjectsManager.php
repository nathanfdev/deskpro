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

namespace DpBehat\Data;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use DeskPRO\Bundle\AppBundle\Entity\ClientDevice;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskAttachment;
use Doctrine\ORM\EntityManager;

/**
 * Class ObjectsManager.
 */
class ObjectsManager
{
    /**
     * @var array Map of record name to its' factory
     */
    private $typeFactories;

    /**
     * @var array Map of record name to its' locator
     */
    private $typeLocators;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->initTypeFactories();
        $this->initTypeLocators();
    }

    /**
     * @param string $type
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return object
     */
    public function create($type, array $data)
    {
        if (!array_key_exists($type, $this->typeFactories)) {
            throw new \Exception("'$type' factory is missing");
        }

        $data = $this->preProcessValues($data);
        $data = DataNormalizer::namedKeysToUnderscore($data);

        $factory = $this->typeFactories[$type];
        if (is_array($factory) && count($factory) > 2) {
            $args    = array_slice($factory, 2);
            $factory = array_slice($factory, 0, 2);
        } else {
            $args = [];
        }
        $args[] = $data;

        $object = call_user_func_array($factory, $args);

        return $object;
    }

    /**
     * @param string $type
     *
     * @throws \Exception
     *
     * @return array
     */
    public function locate($type)
    {
        if (!array_key_exists($type, $this->typeLocators)) {
            throw new \Exception("Locator for the '$type' type is missing");
        }

        $locator = $this->typeLocators[$type];
        if (is_array($locator) && count($locator) > 2) {
            $args    = array_slice($locator, 2);
            $locator = array_slice($locator, 0, 2);
        } else {
            $args = [];
        }
        $objects = call_user_func_array($locator, $args);

        return $objects;
    }

    /**
     * @param string $class
     * @param array  $criteria
     *
     * @return array
     */
    protected function find($class, array $criteria = [])
    {
        return $this->em->getRepository($class)->findBy($criteria);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function preProcessValues(array $data)
    {
        foreach ($data as &$value) {
            if ($value === 'NULL') {
                $value = null;
            }
            if (($date = \DateTime::createFromFormat('Y-m-d G:i:s', $value)) !== false) {
                $value = $date;
            }
        }

        return $data;
    }

    /**
     * Init type factories.
     */
    private function initTypeFactories()
    {
        $this->typeFactories = [
            'Ticket'            => [Factory\CommonFactories::class, 'ticket'],
            'TicketPriority'    => [Factory\SimpleFactory::class, 'create', TicketPriority::class],
            'TicketCategory'    => [Factory\SimpleFactory::class, 'create', TicketCategory::class],
            'TicketWorkflow'    => [Factory\SimpleFactory::class, 'create', TicketWorkflow::class],
            'TicketParticipant' => [Factory\SimpleFactory::class, 'create', TicketParticipant::class],
            'Organization'      => [Factory\SimpleFactory::class, 'create', Organization::class],
            'Chat'              => [Factory\SimpleFactory::class, 'create', ChatConversation::class],
            'AgentTeam'         => [Factory\SimpleFactory::class, 'create', AgentTeam::class],
            'Article'           => [Factory\SimpleFactory::class, 'create', Article::class],
            'News'              => [Factory\SimpleFactory::class, 'create', News::class],
            'Download'          => [Factory\SimpleFactory::class, 'create', Download::class],
            'ArticleCategory'   => [Factory\SimpleFactory::class, 'create', ArticleCategory::class],
            'NewsCategory'      => [Factory\SimpleFactory::class, 'create', NewsCategory::class],
            'DownloadCategory'  => [Factory\SimpleFactory::class, 'create', DownloadCategory::class],
            'Department'        => [Factory\CommonFactories::class, 'department'],
            'CustomDefTicket'   => [Factory\CommonFactories::class, 'customDefTicket'],
            'Task'              => [Factory\CommonFactories::class, 'task'],
            'Product'           => [Factory\CommonFactories::class, 'product'],
            'Sla'               => [Factory\CommonFactories::class, 'sla'],
            'SLA'               => [Factory\CommonFactories::class, 'sla'],
            'User'              => [Factory\PersonFactories::class, 'create', 'user'],
        ];
    }

    /**
     * Init type locators.
     */
    private function initTypeLocators()
    {
        $this->typeLocators = [
            'Ticket'           => [$this, 'find', Ticket::class],
            'SLA'              => [$this, 'find', Sla::class],
            'Organization'     => [$this, 'find', Organization::class],
            'Chat'             => [$this, 'find', ChatConversation::class],
            'Department'       => [$this, 'find', Department::class],
            'CustomDefTicket'  => [$this, 'find', CustomDefTicket::class],
            'Task'             => [$this, 'find', Task::class],
            'Person'           => [$this, 'find', Person::class],
            'Article'          => [$this, 'find', Article::class],
            'News'             => [$this, 'find', News::class],
            'Download'         => [$this, 'find', Download::class],
            'ArticleCategory'  => [$this, 'find', ArticleCategory::class],
            'NewsCategory'     => [$this, 'find', NewsCategory::class],
            'DownloadCategory' => [$this, 'find', DownloadCategory::class],
            'ClientDevice'     => [$this, 'find', ClientDevice::class],
            'User'             => [$this, 'find', Person::class, ['is_agent' => false, 'is_admin' => false]],
            'Agent'            => [$this, 'find', Person::class, ['is_agent' => true, 'is_admin' => false]],
            'Admin'            => [$this, 'find', Person::class, ['is_agent' => false, 'is_admin' => true]],
            'Blob'             => [$this, 'find', Blob::class],
            'TaskAttachment'   => [$this, 'find', TaskAttachment::class],
        ];
    }
}
