<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CompositeTerm extends AbstractTerm implements CompositeTermInterface
{
    /**
     * @var TermInterface[]
     * @Assert\Valid
     */
    protected $terms;

    public function __construct(array $options = [], $op = null)
    {
        parent::__construct($options, $op);
        $this->terms = [];
    }

    public function getTerms()
    {
        return $this->terms;
    }

    public static function configureOptions(TermOptionsResolver $resolver)
    {
    }

    public function addTerm(TermInterface $term)
    {
        $this->terms[] = $term;
    }

    public function replaceTerm(TermInterface $old_term, TermInterface $new_term)
    {
        $key = $this->findTermKey($old_term);

        $this->terms[$key] = $new_term;
    }

    public function removeTerm(TermInterface $term)
    {
        $key = $this->findTermKey($term);

        unset($this->terms[$key]);
    }

    protected function findTermKey(TermInterface $term)
    {
        foreach ($this->terms as $i => $t) {
            if ($term === $t) {
                return $i;
            }
        }

        throw new \InvalidArgumentException('term not found');
    }

    public function getSupportedOps()
    {
        return [TermInterface::OP_OR, TermInterface::OP_AND];
    }

    public function getDefaultOp()
    {
        return TermInterface::OP_OR;
    }
}
