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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Encryption\Form\Type;

use Application\DeskPRO\Encryption\DpEnc;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class DpEncPasswordType extends PasswordType
{
    /**
     * @var DpEnc
     */
    private $enc;

    /**
     * @param DpEnc $enc
     */
    public function __construct(DpEnc $enc)
    {
        $this->enc = $enc;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $enc = $this->enc;

        $builder->addModelTransformer(new CallbackTransformer(
            function ($modelData) use ($enc) {
                return $enc->dpDecrypt($modelData);
            },
            function ($normData) use ($enc) {
                return $enc->dpEncrypt($normData);
            }
        ));

        // the form may already be encrypted
        $builder->addViewTransformer(new CallbackTransformer(
            function ($modelData) use ($enc) {
                return $enc->dpDecrypt($modelData);
            },
            function ($normData) use ($enc) {
                return $enc->dpDecrypt($normData);
            }
        ));
    }

    public function getParent()
    {
        return 'password';
    }

    public function getName()
    {
        return 'dp_enc_password';
    }
}
