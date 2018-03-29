<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class UserRule extends AbstractEntityRepository
{
    /**
     * @return UserRule[]
     */
    public function getAllUserRules()
    {
        return $this->_em->createQuery('
            SELECT u
            FROM DeskPRO:UserRule u
            LEFT JOIN u.add_usergroup ug
        ')->execute();
    }

    /**
     * @return array
     */
    public function getAllUserRulesAsArray()
    {
        $resultData = [];

        $user_rules = $this->_em->createQuery('
            SELECT u
            FROM DeskPRO:UserRule u
            LEFT JOIN u.add_usergroup ug
        ')->execute();

        foreach ($user_rules as $user_rule) {
            $data['id']             = $user_rule->id;
            $data['email_patterns'] = implode(' ', $user_rule->email_patterns);
            $data['run_order']      = $user_rule->run_order;

            $resultData[] = $data;
        }

        return $resultData;
    }

    /**
     * Find all matching rules on an email address.
     *
     * @param $email_address
     *
     * @return array
     */
    public function getMatching($email_address)
    {
        $all = $this->findAll();

        $matching = [];

        foreach ($all as $p) {
            if ($p->isEmailMatch($email_address)) {
                $matching[] = $p;
            }
        }

        return $matching;
    }
}
