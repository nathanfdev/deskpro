<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Publish;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;
use Application\UserBundle\Controller\Helper\ContentRating;

class SaveRating implements PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(Person $person)
    {
        $this->person = $person;
        $this->em     = App::getOrm();
    }

    public function setPersonContext(Person $person)
    {
        $this->person = $this->person;
    }

    public function save($object_type, $object_id, $rating)
    {
        $entity_name    = 'DeskPRO:'.ucfirst($object_type);
        $content_object = $this->em->find($entity_name, $object_id);

        if (!$content_object) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $perm_name = false;
        switch ($entity_name) {
            case 'DeskPRO:Article':  $perm_name = 'articles.rate'; break;
            case 'DeskPRO:Download': $perm_name = 'downloads.rate'; break;
            case 'DeskPRO:News':     $perm_name = 'news.rate'; break;
            case 'DeskPRO:Feedback': $perm_name = 'feedback.rate'; break;
        }

        if ($perm_name) {
            if (!$this->person->hasPerm($perm_name)) {
                throw new \InvalidArgumentException();
            }
        }

        if ($content_object instanceof \Application\DeskPRO\Entity\Feedback) {
            if ($content_object == 'closed') {
                throw new \InvalidArgumentException();
            }
        }

        $content_rating = new ContentRating($content_object, $this->person, null);
        $content_rating->setRequest(App::getRequest());

        $this->em->beginTransaction();
        $content_rating->setRating(
            $rating,
            0
        );
        $this->em->flush();
        $this->em->commit();
    }
}
