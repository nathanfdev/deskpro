<?php

namespace Application\DeskPRO\EntityRepository;

class PageViewLog extends AbstractEntityRepository
{
    /**
     * {@inheritDoc}
     */
    public function getReportAssociations()
    {
        return [
            'person' => [
                'conditions'   => '%2$s.person_id = %1$s.id',
                'targetEntity' => \Application\DeskPRO\Entity\Person::class,
            ],
            'article' => [
                'conditions'   => '%2$s.object_type = 1 AND %2$s.object_id = %1$s.id',
                'targetEntity' => \Application\DeskPRO\Entity\Article::class,
            ],
            'download' => [
                'conditions'   => '%2$s.object_type = 2 AND %2$s.object_id = %1$s.id',
                'targetEntity' => \Application\DeskPRO\Entity\Download::class,
            ],
            'news' => [
                'conditions'   => '%2$s.object_type = 3 AND %2$s.object_id = %1$s.id',
                'targetEntity' => \Application\DeskPRO\Entity\News::class,
            ],
            'topic' => [
                'conditions'   => '%2$s.object_type = 4 AND %2$s.object_id = %1$s.id',
                'targetEntity' => \Application\DeskPRO\Entity\CommunityTopic::class,
            ],
        ];
    }
}
