<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;

use Application\DeskPRO\Brand\BrandStack;
use Application\FormBundle\Validator\Constraints\ValidCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CaptchaType extends AbstractType
{
    const RECAPTCHA_API_SERVER = '//www.google.com/recaptcha/api';

    /**
     * @var string
     */
    protected $public_key;

    /**
     * @var string
     */
    protected $private_key;

    public function __construct(BrandStack $brand_stack)
    {
        $this->public_key = $brand_stack->getActive()->getSetting('core.recaptcha_public_key');
        $this->private_key = $brand_stack->getActive()->getSetting('core.recaptcha_private_key');
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
            'url_challenge' => sprintf('%s/challenge?k=%s', self::RECAPTCHA_API_SERVER, $this->public_key),
            'url_noscript'  => sprintf('%s/noscript?k=%s', self::RECAPTCHA_API_SERVER, $this->public_key),
            'public_key'    => $this->public_key,
        ));
    }

    public function getName()
    {
        return 'deskpro_captcha';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('attr' => array(
                'options'     => array(
                    'theme' => 'clean',
                ),
                'empty_data'  => null,
                'mapped'      => false,
                'constraints' => array(
                    new ValidCaptcha()
                )

            ))
        );
    }
}
