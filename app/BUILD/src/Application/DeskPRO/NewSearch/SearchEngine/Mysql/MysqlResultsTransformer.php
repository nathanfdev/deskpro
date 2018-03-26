<?php

namespace Application\DeskPRO\NewSearch\SearchEngine\Mysql;

use Doctrine\ORM\EntityManager;

class MysqlResultsTransformer
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param array $results
     *
     * @return array
     */
    public function transform(array $results)
    {
        //------------------------------
        // Sort results by type so we can fetch
        // results from db in one go
        //------------------------------

        $ent_ids = [];
        foreach ($results as $hit) {
            $ent = $this->getEntityFromType($hit['object_type']);
            if (!isset($ent_ids[$ent])) {
                $ent_ids[$ent] = [];
            }

            $ent_ids[$ent][] = $hit['object_id'];
        }

        //------------------------------
        // Fetch results from db
        //------------------------------

        $objects = [];
        foreach ($ent_ids as $ent => $ids) {
            $ent_objects = $this->em->getRepository($ent)->getByIds($ids, true);
            if ($ent_objects) {
                foreach ($ent_objects as $o) {
                    $key           = $ent.':'.$o->id;
                    $objects[$key] = $o;
                }
            }
        }

        //------------------------------
        // Finally sort into one main array
        //------------------------------

        $sorted_objects = [];
        foreach ($results as $hit) {
            $ent = $this->getEntityFromType($hit['object_type']);
            $key = $ent.':'.$hit['object_id'];
            if (isset($objects[$key])) {
                $sorted_objects[] = $objects[$key];
            }
        }

        return $sorted_objects;
    }

    /**
     * @param string $type
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private function getEntityFromType($type)
    {
        //todo this should be generalised somewhere

        switch ($type) {
            case 'article':           return 'DeskPRO:Article';
            case 'download':          return 'DeskPRO:Download';
            case 'news':              return 'DeskPRO:News';
            case 'feedback':          return 'DeskPRO:Feedback';
            case 'topic':             return 'DeskPRO:Topic';
            case 'chat_conversation': return 'DeskPRO:ChatConversation';
            case 'person':            return 'DeskPRO:Person';
            case 'ticket':            return 'DeskPRO:Ticket';
            default:
                throw new \InvalidArgumentException();
        }
    }
}
