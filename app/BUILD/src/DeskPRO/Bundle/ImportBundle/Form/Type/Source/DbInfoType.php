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

namespace DeskPRO\Bundle\ImportBundle\Form\Type\Source;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DbInfoType.
 */
class DbInfoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host', TextType::class, [
                'empty_data'  => 'localhost',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('port', TextType::class, [
                'empty_data'  => '3306',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('user', TextType::class, [
                'empty_data'  => 'root',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('password', TextType::class)
            ->add('dbname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('driver', TextType::class, [
                'empty_data' => 'pdo_mysql',
            ])
            ->add('charset', TextType::class, [
                'empty_data' => 'utf8',
            ])
        ;
    }
}
