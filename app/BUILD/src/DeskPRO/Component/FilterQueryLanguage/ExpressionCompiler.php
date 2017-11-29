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

namespace DeskPRO\Component\FilterQueryLanguage;

/**
 * Takes a FQL query and compiles an expression where field values are all contained under 'fields', and
 * user-vars are just variables.
 */
class ExpressionCompiler
{
    /**
     * @param array $queryParts
     */
    public function compile(array $root)
    {
        $expr = $this->walk($root);

        if ($expr[0] === '(') {
            $expr = substr($expr, 1, -1);
        }

        return $expr;
    }

    private function walk($part)
    {
        switch ($part['type']) {
            case 'BOOL':
                return $this->walkBool($part);
            case 'TERM':
                return $this->walkTerm($part);
            case 'VALUE':
                return $this->walkValue($part);
        }
    }

    private function walkBool(array $boolPart)
    {
        $expr = [];
        foreach ($boolPart['terms'] as $term) {
            $expr[] = $this->walkTerm($term);
        }

        if ($boolPart['operator'] === 'NOT') {
            // SHOULD only be a single expr here, user would sub-nest another bool
            // to get inner checks
            return '!('.implode(' OR ', $expr).')';
        }

        return '('.implode(" {$boolPart['operator']} ", $expr).')';
    }

    private function walkTerm(array $termPart)
    {
        if ($termPart['type'] === 'BOOL') {
            return $this->walkBool($termPart);
        }

        $fieldExpr = $this->buildFieldExpr($termPart['field']);

        switch ($termPart['operator']) {
            case '=':
            case '!=':
            case '<':
            case '<=':
            case '>':
            case '>=':
                return $fieldExpr.' '.$termPart['operator'].' '.$this->walkValue($termPart['options']['value']);

            case 'IN':
            case 'NOT_IN':
                $values = [];
                foreach ($termPart['options']['values'] as $v) {
                    $values[] = $this->walkValue($v);
                }

                $expr = $fieldExpr.' in ['.implode(', ', $values).']';

                if ($termPart['operator'] === 'NOT_IN') {
                    $expr = "!($expr)";
                }

                return $expr;
        }

        return false;
    }

    private function buildFieldExpr(array $field)
    {
        return 'fields.'.$field['identity'];
    }

    private function walkValue(array $valuePart)
    {
        switch ($valuePart['valueType']) {
            case 'STRING':
            case 'NUMERIC':
            case 'BOOLEAN':
                return var_export($valuePart['value'], true);

            case 'VAR':
                return $valuePart['identity'];

            case 'FUNC':
                $params = [];
                foreach ($valuePart['params'] as $v) {
                    $params[] = $this->walkValue($v);
                }

                $params = implode(', ', $params);

                return $valuePart['name']."($params)";
        }
    }
}
