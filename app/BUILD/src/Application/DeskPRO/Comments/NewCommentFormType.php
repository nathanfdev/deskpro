<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Comments;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewCommentFormType extends AbstractType
{
    /** @var Person */
    protected $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->person['id']) {
            $builder->add('email', 'text');
        }

        $builder->add('name', 'text');
        $builder->add('content', 'textarea');
    }

    public function getName()
    {
        return 'new_comment';
    }
}
