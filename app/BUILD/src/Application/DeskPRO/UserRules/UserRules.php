<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\UserRules;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\UserRule;
use Doctrine\ORM\EntityManager;

class UserRules
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\UserRule[]
     */
    protected $user_rules;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads data from the database.
     */
    private function preload()
    {
        if ($this->user_rules !== null) {
            return;
        }

        $this->user_rules = $this->em->getRepository('DeskPRO:UserRule')->getAllUserRules();
    }

    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */
    public function reset()
    {
        $this->user_rules = null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\UserRule
     */
    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:UserRule')->get($id);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getWithUsergroup($id)
    {
        $user_rule = $this->em->getRepository('DeskPRO:UserRule')->get($id);

        $resultData = [];

        if ($user_rule) {
            $data['id']             = $user_rule->id;
            $data['email_patterns'] = $user_rule->email_patterns;
            $data['run_order']      = $user_rule->run_order;

            if (is_array($data['email_patterns'])) {
                $data['email_patterns'] = implode("\n", $user_rule->email_patterns);
            }

            if ($user_rule->add_usergroup) {
                $data['usergroup']['id']    = $user_rule->add_usergroup->id;
                $data['usergroup']['title'] = $user_rule->add_usergroup->title;
            }

            $resultData = $data;
        }

        return $resultData;
    }

    /**
     * @return \Application\DeskPRO\Entity\UserRule[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->user_rules;
    }

    /**
     * @return array
     */
    public function getAllAsArray()
    {
        return $this->em->getRepository('DeskPRO:UserRule')->getAllUserRulesAsArray();
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->user_rules);
    }

    /**
     * @return \Application\DeskPRO\Entity\UserRule
     */
    public function createNew()
    {
        return UserRule::createUserRule();
    }

    /**
     * @param UserRule $user_rule
     * @param int      $page
     *
     * @return array
     */
    public function applyRuleToUsers(UserRule $user_rule, $page)
    {
        $per_page = 250;
        $page     = (int) $page;
        $offset   = $page * $per_page;

        $num_pages = ceil(App::getDb()->fetchColumn('
			SELECT COUNT(*)
			FROM people_emails
			WHERE is_validated = 1
		') / $per_page);

        $email_to_user = App::getDb()->fetchAllKeyValue("
            SELECT email, person_id
            FROM people_emails
            WHERE is_validated = 1
            ORDER BY id ASC
			LIMIT $offset, $per_page
        ");

        if (!$email_to_user) {
            return ['completed' => true];
        }

        $did_user = [];
        $batch    = [];

        foreach ($email_to_user as $email => $user_id) {
            if (isset($did_user[$user_id])) {
                continue;
            }

            if ($user_rule->isEmailMatch($email)) {
                $did_user[$user_id] = true;

                if ($user_rule->add_organization) {
                    App::getDb()->update(
                        'people',
                        [
                             'organization_id' => $user_rule->add_organization->id,
                        ],
                        ['id' => $user_id]
                    );
                }

                if ($user_rule->add_usergroup) {
                    $batch[] = [
                        'person_id'    => $user_id,
                        'usergroup_id' => $user_rule->add_usergroup->id,
                    ];
                }
            }
        }

        if ($batch) {
            App::getDb()->batchInsert('person2usergroups', $batch, true);
        }

        return [
            'completed' => false,
            'success'   => true,
            'page'      => $page + 1,
            'num_pages' => $num_pages,
        ];
    }
}
