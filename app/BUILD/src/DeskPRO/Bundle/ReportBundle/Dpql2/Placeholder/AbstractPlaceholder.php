<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Orb\Util\Strings;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Abstract base for all placeholder (%NAME%) references.
 */
abstract class AbstractPlaceholder implements DpqlPlaceholderInterface
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param TokenStorage $tokenStorage
     */
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getName()
    {
        $reflection = new \ReflectionClass(static::class);

        $func = Strings::camelCaseToUnderscore($reflection->getShortName());
        $func = strtoupper($func);

        return $func;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareWithIntervals(SqlSelect $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result, array $intervals = [])
    {
        return $this->prepare($statement, $section, $stack, $select, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, SelectPart $statement, $section, array $stack,
        SqlSelect $select, ResultMetadata $result, array $intervals = []
    ) {
        return false;
    }

    /**
     * Gets the placeholder in DPQL.
     *
     * @return string
     */
    protected function _toDpql()
    {
        $reflection = new \ReflectionClass($this);

        $name = $reflection->getShortName();
        $name = Strings::camelCaseToUnderscore($name);
        $name = strtoupper($name);

        return '%'.$name.'%';
    }

    /**
     * @return Person|null
     */
    protected function getPerson()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $person = $token->getUser();
        if (!$person instanceof Person) {
            return;
        }

        return $person;
    }
}
