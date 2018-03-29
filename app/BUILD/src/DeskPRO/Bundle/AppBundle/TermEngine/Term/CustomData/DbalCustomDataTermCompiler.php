<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class DbalCustomDataTermCompiler.
 */
class DbalCustomDataTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $fieldId = $term->getOption('field_id');
        $value   = $term->getOption('custom_data_value');

        /** @var CustomDefTicket $def */
        $def = $this->em->getRepository(CustomDefTicket::class)->find($fieldId);
        if (!$def) {
            throw new BadRequestHttpException("Custom field with {$fieldId} doesn't exist.");
        }

        $qp = new DbalQueryPart();
        $qp->addUniqueJoin(
            'custom_data_ticket',
            'custom_data_ticket',
            '{custom_data_ticket}.ticket_id = ticket.id AND
             {custom_data_ticket}.root_field_id = '.intval($fieldId)
        );
        $qp->addUniqueJoin(
            'custom_def_ticket',
            'custom_def_ticket',
            '{custom_data_ticket}.root_field_id = {custom_def_ticket}.id'
        );

        if ($def->isDateType()) {
            if (isset($value['from']) || isset($value['to'])) {
                $minValue = strtotime(isset($value['from']) ? $value['from'] : null);
                $maxValue = strtotime(isset($value['to']) ? $value['to'] : null);

                if ($minValue) {
                    $qp->setWhereString('{custom_data_ticket}.value >= :min_value');
                    $qp->setParameter('min_value', $minValue);
                }

                if ($maxValue) {
                    $qp->setWhereString('{custom_data_ticket}.value <= :max_value');
                    $qp->setParameter('max_value', $maxValue);
                }
            } elseif (is_scalar($value)) {
                $periodDql = DateHelper::getDatePeriodCaseWhenDql('{custom_data_ticket}.value', 'timestamp');
                $qp->setWhereString("$periodDql = :custom_data_value");
                $qp->setParameter('custom_data_value', $value);
            } else {
                throw new BadRequestHttpException("Date period expected to be a string or contains 'from' and 'to' params.");
            }
        } else {
            $qp->setParameter('custom_data_value', $value);

            if ($def->getWidgetType() === CustomDefAbstract::TYPE_TOGGLE) {
                $qp->setWhereString('{custom_data_ticket}.value = :custom_data_value');
            } elseif ($def->isChoiceType()) {
                $qp->setWhereString('{custom_data_ticket}.field_id IN(:custom_data_value)');
            } elseif (in_array($def->getWidgetType(), [CustomDefAbstract::TYPE_TEXT, CustomDefAbstract::TYPE_TEXTAREA])) {
                $qp->setWhereString('{custom_data_ticket}.input LIKE :custom_data_value');
                $qp->setParameter('custom_data_value', "%{$value}%");
            } else {
                $qp->setWhereString('CASE WHEN {custom_data_ticket}.value > 0 THEN {custom_data_ticket}.value ELSE {custom_data_ticket}.input END = :custom_data_value');
            }
        }

        $this->logQueryPart($qp);

        return $qp;
    }
}
