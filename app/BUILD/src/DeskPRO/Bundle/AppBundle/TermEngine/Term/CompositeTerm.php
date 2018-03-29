<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CompositeTerm.
 */
class CompositeTerm extends AbstractTerm implements CompositeTermInterface
{
    /**
     * @var TermInterface[]
     * @Assert\Valid
     */
    protected $terms;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = [], $op = null)
    {
        parent::__construct($options, $op);
        $this->terms = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addTerm(TermInterface $term)
    {
        $this->terms[] = $term;
    }

    /**
     * @param TermInterface $old_term
     * @param TermInterface $new_term
     */
    public function replaceTerm(TermInterface $old_term, TermInterface $new_term)
    {
        $key = $this->findTermKey($old_term);

        $this->terms[$key] = $new_term;
    }

    /**
     * @param TermInterface $term
     */
    public function removeTerm(TermInterface $term)
    {
        $key = $this->findTermKey($term);

        unset($this->terms[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedOps()
    {
        return [TermInterface::OP_OR, TermInterface::OP_AND];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOp()
    {
        return TermInterface::OP_OR;
    }

    /**
     * @param TermInterface $term
     *
     * @return int|string
     */
    protected function findTermKey(TermInterface $term)
    {
        foreach ($this->terms as $i => $t) {
            if ($term === $t) {
                return $i;
            }
        }

        throw new \InvalidArgumentException('term not found');
    }
}
