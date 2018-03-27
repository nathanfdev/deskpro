<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Traits;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketSearchTrait.
 *
 * @method object                findOr404()
 * @method EntityManager         getManager()
 * @method NotFoundHttpException createNotFoundException()
 */
trait TicketSearchTrait
{
    /**
     * {@inheritdoc}
     */
    protected function findOr404($class, $id, $message = null)
    {
        if ($class !== Ticket::class) {
            return parent::findOr404($class, $id, $message);
        }

        $entity = null;
        if (\Orb\Util\Numbers::isInteger($id)) {
            $entity = $this->getManager()->getRepository($class)->find($id);
        } elseif (strpos($id, 'ref:') === 0) {
            $entity = $this->getManager()
                ->getRepository($class)
                ->findOneByRef(substr($id, strlen('ref:')));
        }

        if (!$entity) {
            throw $this->createNotFoundException($message ?: "#{$id} Not Found");
        }

        return $entity;
    }
}
