<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class ApiToken extends AbstractEntityRepository
{
    /**
     * Find an API key based off of a key string. A key string is: "id:code".
     *
     * @param string $api_string
     *
     * @return \Application\DeskPRO\Entity\ApiToken
     */
    public function findByTokenString($token_string)
    {
        if (strpos($token_string, ':') === false) {
            return;
        }

        list($id, $token) = explode(':', $token_string, 2);

        $token_obj = $this->find($id);
        if (!$token_obj) {
            return;
        }
        if ($token_obj->token != $token) {
            return;
        }

        return $token_obj;
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return null|\Application\DeskPRO\Entity\ApiToken
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTokenForPerson(\Application\DeskPRO\Entity\Person $person)
    {
        return $this->getEntityManager()
            ->createQuery("
                SELECT t
                FROM DeskPRO:ApiToken t
                WHERE t.person = ?0 AND t.scope = 'client'
            ")
            ->setParameters([$person])
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
