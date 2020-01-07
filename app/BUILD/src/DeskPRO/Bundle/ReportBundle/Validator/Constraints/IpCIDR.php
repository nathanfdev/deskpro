<?php

namespace DeskPRO\Bundle\ReportBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Ip;

/**
 * Class IpCIDR
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @package DeskPRO\Bundle\ReportBundle\Validator\Constraints
 */
class IpCIDR extends Ip
{
}
