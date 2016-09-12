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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm
 */
class CompositeTermSpec extends ObjectBehavior
{
    public function it_is_a_term()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermInterface');
    }

    public function it_is_a_collection_of_terms(TermInterface $term1, TermInterface $term2)
    {
        $this->getTerms()->shouldBe([]);
        $this->addTerm($term1);
        $this->getTerms()->shouldBe([$term1]);
        $this->addTerm($term2);
        $this->getTerms()->shouldBe([$term1, $term2]);
    }

    public function it_defaults_to_or_op()
    {
        $this->getOp()->shouldReturn(TermInterface::OP_OR);
    }

    public function it_lets_you_change_the_op()
    {
        $this->setOp(TermInterface::OP_AND);

        $this->getOp()->shouldReturn(TermInterface::OP_AND);
    }

    public function it_has_no_options()
    {
        $resolver = $this->getOptionsResolver();
        $resolver->getDefinedOptions()->shouldBe([]);
    }

    public function it_lets_you_add_a_term(
        TermInterface $term1
    ) {
        $this->getTerms()->shouldBe([]);

        $this->addTerm($term1);

        $this->getTerms()->shouldBe([$term1]);
    }

    public function it_lets_you_remove_a_term(
        TermInterface $term1
    ) {
        $this->addTerm($term1);

        $this->getTerms()->shouldBe([$term1]);

        $this->removeTerm($term1);

        $this->getTerms()->shouldBe([]);
    }

    public function it_lets_you_replace_a_term(
        TermInterface $term1,
        TermInterface $term2,
        TermInterface $term3,
        TermInterface $term4
    ) {
        $this->addTerm($term1);
        $this->addTerm($term2);
        $this->addTerm($term3);

        $this->getTerms()->shouldBe([$term1, $term2, $term3]);

        $this->replaceTerm($term2, $term4);

        $this->getTerms()->shouldBe([$term1, $term4, $term3]);
    }
}
