<?php

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
