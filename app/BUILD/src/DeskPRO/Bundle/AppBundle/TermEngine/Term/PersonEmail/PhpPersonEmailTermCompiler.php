<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpPersonEmailTermCompiler.
 */
class PhpPersonEmailTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return new PhpCheck('check_contains(ticket.getPersonEmailAddress(), :op, :email)', [
            'op'    => $term->getOp(),
            'email' => $term->getOption('email'),
        ]);
    }
}
