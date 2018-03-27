<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TwitterUser extends AbstractEntityRepository
{
    public function getByScreenName($name, $pull_from_api = false)
    {
        $em = $this->getEntityManager();

        $user = $em->createQuery('
            SELECT u
            FROM DeskPRO:TwitterUser u
            WHERE u.screen_name = ?0
        ')->setParameters([$name])->getOneOrNullResult();
        if (!$user && $pull_from_api) {
            $account = $em->getRepository('DeskPRO:TwitterAccount')->getFirst();
            if ($account) {
                try {
                    $response = $account->getTwitterApi()->get_usersShow(['screen_name' => $name]);
                    if ($response->id_str) {
                        $user = \Application\DeskPRO\Entity\TwitterUser::createFromJson($response);
                    }
                } catch (\EpiTwitterException $e) {
                } catch (\EpiOAuthException $e) {
                }
            }
        }

        return $user;
    }
}
