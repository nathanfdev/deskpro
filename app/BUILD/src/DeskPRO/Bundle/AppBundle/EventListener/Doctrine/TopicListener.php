<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Topic;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TopicListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist', 'preUpdate',
        ];
    }

    /**
     * @internal
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Topic) {
            return;
        }

        $em = $args->getEntityManager();
        if ($args->hasChangedField('content')) {
            $content = $this->cacheImagesSize($args->getNewValue('content'), $em);
            $args->setNewValue('content', $content);
        }
    }

    /**
     * @internal
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Topic) {
            return;
        }

        $em = $args->getEntityManager();
        if ($args->hasChangedField('content')) {
            $content = $this->cacheImagesSize($args->getNewValue('content'), $em);
            $args->setNewValue('content', $content);
            $entity->setContent($content);
        }
    }

    public function cacheImagesSize($content, EntityManager $em)
    {
        if (preg_match_all('/<img[^>]+>/', $content, $matches)) {
            $imgs = $matches[0];
            foreach ($imgs as $img) {
                preg_match('/img\(([A-Z0-9]+)\//', $img, $matches);
                if ($matches) {
                    $blobAuth = $matches[1];
                    /** @var Blob $blob */
                    $blob = $em->getRepository(Blob::class)->getByAuthId($blobAuth);
                    if (!$blob) {
                        continue;
                    }
                    preg_match_all('/(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?/', $img, $matches);
                    $attributes = [];
                    for ($i = 0; $i < count($matches[0]); ++$i) {
                        $attributes[$matches[1][$i]] = $matches[2][$i];
                    }
                    $attributes['data-with']   = $blob->getDimW();
                    $attributes['data-height'] = $blob->getDimH();
                    $attributes['data-src']    = $attributes['src'];
                    $attributes['src']         = $this->generateSvg($blob->getDimW(), $blob->getDimH());
                    $newImg                    = '<img '.implode(' ', array_map(function ($k, $v) {
                        return "$k=\"$v\"";
                    }, array_keys($attributes), array_values($attributes))).' />';
                    $content = str_replace($img, $newImg, $content);
                }
            }
        }

        return $content;
    }

    private function generateSvg($width, $height)
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 '.$width.' '.$height.'" ><rect x="0" y="0" style="fill:white;stroke:grey;stroke-width:3px;" id="e1_rectangle" width="'.$width.'" height="'.$height.'"/></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
