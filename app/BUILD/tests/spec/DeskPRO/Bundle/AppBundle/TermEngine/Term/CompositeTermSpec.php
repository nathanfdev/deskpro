<?php

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
