<?php

namespace DeskPRO\Bundle\ReportBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Ip;

/**
 * Class IpCIDR
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class IpCIDR extends Ip
{
}
