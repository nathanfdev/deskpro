<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Webhooks;

use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\CustomOrganizationFieldDefinitionAlias;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\CustomPeopleFieldDefinitionAlias;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\CustomTicketFieldDefinitionAlias;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\Repository;
use DeskPRO\Bundle\AppBundle\ObjectAlias;
use Doctrine\ORM;

class SearchTermsAliasResolver
{
    /**
     * @param ORM\EntityManager $em
     * @return SearchTermsAliasResolver
     */
    public static function create(ORM\EntityManager $em)
    {
        $aliasTypes = [
            'ticket_field' => CustomTicketFieldDefinitionAlias::class,
            'person_field' => CustomPeopleFieldDefinitionAlias::class,
            'org_field' => CustomOrganizationFieldDefinitionAlias::class
        ];

        $defaultStrategies = [];
        foreach ($aliasTypes as $aliasType => $entityType) {
            /** @var Repository $repository */
            $repository = $em->getRepository($entityType);
            $defaultStrategies[$aliasType] = new ObjectAlias\DefaultIdResolvingStrategy($repository);
        }

        return new SearchTermsAliasResolver($defaultStrategies);
    }

    /**
     * @param array $searchTerm a list of legacy search terms
     * @return bool
     */
    public static function isAliasTerm(array $searchTerm)
    {
        $type = null;
        $op = null;
        $options = null;
        extract($searchTerm, EXTR_OVERWRITE);

        if (! is_string($type) || !is_string($op) || !is_array($options)) {
            return false;
        }

        $aliasTypes = ['ticket_field', 'person_field', 'org_field'];
        if (!in_array($type, $aliasTypes)) {
            return false;
        }

        $field = null;
        $value = null;
        extract($options, EXTR_OVERWRITE);
        if (! is_string($field) || empty($field) || is_null($value)) {
            return false;
        }

        return true;
    }

    /**
     * @var array|ObjectAlias\DefaultIdResolvingStrategy[]
     */
    private $defaultIdResolvingStrategies;

    /**
     * SearchTermsAliasResolver constructor.
     * @param array|ObjectAlias\DefaultIdResolvingStrategy[] $defaultIdResolvingStrategies
     */
    public function __construct(array $defaultIdResolvingStrategies)
    {
        $this->defaultIdResolvingStrategies = $defaultIdResolvingStrategies;
    }

    /**
     * @param array $searchTerms
     * @return array
     */
    public function filterAliasTerms(array $searchTerms)
    {
        return array_filter($searchTerms, function ($term) {
            if (is_array($term)) {
                return SearchTermsAliasResolver::isAliasTerm($term);
            }
            return false;
        });
    }

    /**
     * @param array $searchTerms
     * @return array
     */
    public function resolveAliasTerms(array $searchTerms)
    {
        $aliasTerms = $this->filterAliasTerms($searchTerms);
        if (empty($aliasTerms)) {
            return $searchTerms;
        }

        $resolvedAliases = [];
        foreach ($aliasTerms as $key => $term) {
            $resolvedAliases[$key] = $this->resolveTerm($term);
        }

        // merge resolved aliases into search terms, creating the final list
        $resolvedTerms = array_map(function($term) {
            return $term;
        }, $searchTerms);
        foreach ($resolvedAliases as $key => $value) {
            $resolvedTerms[$key] = $value;
        }

        return $resolvedTerms;
    }

    /**
     * @param array $term
     * @return array|null
     */
    public function resolveTerm(array $term)
    {
        if (!  SearchTermsAliasResolver::isAliasTerm($term)) {
            return null;
        }

        $type = $term['type'];
        $alias = $term['options']['field'];
        $id = $this->resolveId($type, $alias);

        if (empty($id)) {
            return null;
        }

        return [
            'type' => $type."[$id]",
            'op' => $term['op'],
            'options' => [
                'custom_fields' => [
                    "field_$id" => $term['options']['value']
                ]
            ],
        ];
    }

    /**
     * @param string $fieldType
     * @param string $fieldAlias
     * @return null|string
     */
    private function resolveId($fieldType, $fieldAlias)
    {
        $strategies = [
            new ObjectAlias\FieldIdResolvingStrategy(),
        ];

        /** @var ObjectAlias\DefaultIdResolvingStrategy $defaultStrategy */
        $defaultStrategy = null;
        if (array_key_exists($fieldType, $this->defaultIdResolvingStrategies)) {
            $defaultStrategy = $this->defaultIdResolvingStrategies[$fieldType];
        }

        if ($defaultStrategy) {
            $strategies[] = $defaultStrategy;
        }

        $fieldId = null;
        foreach($strategies as $strategy) {
            $fieldId = $strategy->resolve($fieldAlias);
            if (!empty($fieldId)) {
                break;
            }
        }

        return empty($fieldId) ? null : $fieldId;
    }
}
