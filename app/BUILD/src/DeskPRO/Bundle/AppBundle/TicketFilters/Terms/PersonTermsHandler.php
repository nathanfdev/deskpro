<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\EntityRepository\Person as PersonRepos;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\OptValue\OptValue;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\Util\ListUtils;
use Orb\Validator\StringEmail;

class PersonTermsHandler extends AbstractTermsHandler
{
    /**
     * @var PersonRepos
     */
    private $personRepos;

    /**
     * PersonTermsHandler constructor.
     *
     * @param PersonRepos $personRepos
     */
    public function __construct(PersonRepos $personRepos)
    {
        $this->personRepos = $personRepos;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            TermFieldIds::PERSON_ID,
            //Terms::PERSON_LABELS,
            //Terms::PERSON_USERGROUPS,
        ];
    }

    public function doesTicketMatch($fieldId, $operator, OptValue $options, TicketModel $ticketModel, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::PERSON_ID:
                $value = $this->normalizePersonId($options->getValue());

                return $this->checkValue($ticketModel->person->id, $operator, $value);

            default:
                throw new \InvalidArgumentException();
        }
    }

    public function buildQueryCondition($fieldId, $operator, OptValue $options, Context $context, Term $term)
    {
        switch ($fieldId) {
            case TermFieldIds::PERSON_ID:
                $value = $this->normalizePersonId($options->getValue());

                return $this->checkValueQueryCondition('{tickets}.person_id', $operator, $value);

            default:
                throw new \InvalidArgumentException();
        }
    }

    private function normalizePersonId($value)
    {
        if (is_array($value)) {
            return ListUtils::map($value, [$this, 'normalizePersonId']);
        }

        if (is_numeric($value) || ctype_digit($value)) {
            return $value;
        } elseif (StringEmail::isValueValid($value)) {
            $person = $this->personRepos->findOneByEmail($value);
            if ($person) {
                return $person->getId();
            }
        }

        return 0;
    }
}
