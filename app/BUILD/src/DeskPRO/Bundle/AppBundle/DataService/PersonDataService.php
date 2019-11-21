<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\Person as PersonRepo;
use Orb\Validator\StringEmail;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class PersonDataService.
 */
class PersonDataService extends AbstractDataService
{
    /**
     * @param mixed $person right now only ID is useful
     *
     * @return Person|null
     */
    public function getPerson($person)
    {
        return $this->getPersonRepo()->find($person);
    }

    /**
     * @param $email
     *
     * @return Person|null
     */
    public function getPersonForEmail($email)
    {
        // using caution and not caching most PersonDataService methods
        return $this->getPersonRepo()->findOneByEmail($email);
    }

    /**
     * @param Person $person
     *
     * @return bool
     */
    public function isPasswordResetReSendExpired(Person $person = null)
    {
        $tmpData = $this->em->getRepository(TmpData::class)->findOneBy([
            'name' => 'reset-password-'.$person->getId(),
        ]);

        return !$tmpData || new \DateTime('-1 hour') > $tmpData->getDateCreated();
    }

    /**
     * @param Person     $person
     * @param string|int $expire
     *
     * @return array
     */
    public function createPasswordReset(Person $person, $expire = null)
    {
        $name = 'reset-password-'.$person->getId();

        // only 1 valid at a time
        $this->em->getConnection()->delete('tmp_data', ['name' => $name]);

        $tmpData = TmpData::create('reset-password', ['person' => $person->getId()], $expire ?: '+1 day', $name);
        $this->em->persist($tmpData);
        $this->em->flush();

        return [
            'person'         => $person,
            'tmpdata'        => $tmpData,
            'code'           => $tmpData->getCode(),
            'date_requested' => $tmpData->getDateCreated(),
        ];
    }

    /**
     * @param string $code
     *
     * @return Person|null
     */
    public function findPasswordReset($code)
    {
        // using caution and not caching most PersonDataService methods
        if (strlen($code) > 0) {
            $tmpdata = $this->getTmpDataRepo()->getByCode($code, 'reset-password');
            if ($tmpdata) {
                $person = $this->getPersonRepo()->find($tmpdata->getData('person', 0));
                if ($person) {
                    return [
                        'person'         => $person,
                        'tmpdata'        => $tmpdata,
                        'code'           => $tmpdata->getCode(),
                        'date_requested' => $tmpdata->getDateCreated(),
                    ];
                }
            }
        }

        return;
    }

    /**
     * Clear a used password reset code.
     *
     * @param array $password_reset
     */
    public function clearPasswordReset(array $password_reset)
    {
        $this->em->remove($password_reset['tmpdata']);
        $this->em->flush();
    }

    /**
     * @param Person $member
     * @param int    $page
     * @param int    $maxPerPage
     * @param bool   $groupChronologically Group results by today, this week, this month, etc
     *
     * @return Pagerfanta
     */
    public function getPortalMemberActivitiesPager(Person $member, $page, $maxPerPage, $groupChronologically = false)
    {
        $params = [
            'subLimit'            => 250,
            'truncateDescription' => 200,
            'personId'            => (int) $member->getId(),
        ];

        $types = [
            'subLimit'            => 'integer',
            'truncateDescription' => 'integer',
            'personId'            => 'integer',
        ];

        $sqlTemplate = file_get_contents(__DIR__.'/sql/member_profile_activities.sql');

        $group = function ($activities) {
            $groups = [];
            $today  = (new \DateTimeImmutable())
                ->setTime(0, 0, 0)
            ;

            foreach ($activities as $activity) {
                $date = (new \DateTimeImmutable($activity['date']))
                    ->setTime(0, 0, 0)
                ;

                if ($date == $today) {
                    $groups['today'][] = $activity;
                } elseif ($date >= $today->modify('monday this week')) {
                    $groups['this_week'][] = $activity;
                } elseif ($date >= $today->modify('monday last week')) {
                    $groups['last_week'][] = $activity;
                } elseif ($date >= $today->modify('first day of this month')) {
                    $groups['this_month'][] = $activity;
                } elseif ($date >= $today->modify('first day of last month')) {
                    $groups['last_month'][] = $activity;
                } elseif ($date >= $today->modify(sprintf('first day of january %d', $today->format('Y')))) {
                    $groups['this_year'][] = $activity;
                } elseif ($date >= $today->modify(sprintf('first day of january %d', $today->format('Y') - 1))) {
                    $groups['last_year'][] = $activity;
                } else {
                    $groups['everything_else'][] = $activity;
                }
            }

            return $groups;
        };

        $count = function () use ($sqlTemplate, $params, $types) {
            return (int) $this
                ->em
                ->getConnection()
                ->executeQuery($this->parseMemberActivitiesSql($sqlTemplate, 'COUNT(*)'), $params, $types)
                ->fetchColumn(0)
            ;
        };

        $slice = function ($offset, $length) use ($sqlTemplate, $params, $types, $group, $groupChronologically) {
            $activities = $this
                ->em
                ->getConnection()
                ->executeQuery($this->parseMemberActivitiesSql(
                    $sqlTemplate,
                    't.id, t.slug, t.type, t.date, t.description', 'ORDER BY t.date DESC',
                    sprintf('LIMIT %d, %d', $offset, $length)
                ), $params, $types)
                ->fetchAll()
            ;

            if ($groupChronologically) {
                $activities = $group($activities);
            }

            return $activities;
        };

        $pager = new Pagerfanta(new CallbackAdapter($count, $slice));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param type $page
     * @param type $maxPerPage
     *
     * @return Pagerfanta
     */
    public function getPortalMembersPager($page, $maxPerPage, $orderBy = null, $search = '')
    {
        $em = $this->em;
        $qb = $em->createQueryBuilder();

        $qb->select('p')
            ->from(Person::class, 'p')
            ->where('p.is_deleted = 0')
            ->andWhere('p.is_agent = 0')
            ->andWhere('p.is_user = 1');

        if ($search) {
            $qb->join('p.emails', 'eml');

            if (StringEmail::isValueValid($search)) {
                $qb->andWhere('eml.email = :email');
                $qb->setParameter('email', $search);
            } else {
                $qb->andWhere('p.override_display_name LIKE :override OR p.first_name LIKE :first OR p.last_name LIKE :last OR eml.email LIKE :email');
                $qb->setParameters([
                    'override' => '%'.$search.'%',
                    'first'    => '%'.$search.'%',
                    'last'     => '%'.$search.'%',
                    'email'    => '%'.$search.'%',
                ]);
            }
        }

        $qbCount = clone $qb;
        $qbCount->select('count(p.id)');
        $cnt = $qbCount->getQuery()->getSingleScalarResult();

        switch ($orderBy) {
            case 'last_name':
                $orderBy = 'last_name';
                break;
            case 'first_name':
                $orderBy = 'first_name';
                break;
            default:
                $orderBy = 'id';
        }

        if ($cnt < 5000) {
            $qb->orderBy('p.'.$orderBy, 'ASC');
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\TmpData
     */
    private function getTmpDataRepo()
    {
        return $this->em->getRepository('DeskPRO:TmpData');
    }

    /**
     * @return PersonRepo
     */
    public function getPersonRepo()
    {
        return $this->em->getRepository('DeskPRO:Person');
    }

    /**
     * Parse the SQL template that builds the unified list of member activities.
     *
     * @param string $template
     * @param string $columns
     * @param string $order
     * @param string $limit
     *
     * @return string
     */
    private function parseMemberActivitiesSql($template, $columns, $order = '', $limit = '')
    {
        return str_replace(
            ['{columns}', '{order}', '{limit}'],
            [$columns, $order, $limit],
            $template
        );
    }
}
