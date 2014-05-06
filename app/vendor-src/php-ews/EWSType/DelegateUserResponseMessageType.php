<?php
/**
 * Contains EWSType_DelegateUserResponseMessageType.
 */

/**
 * contains the response message for a single delegate user.
 *
 * @package php-ews\Types
 */
class EWSType_DelegateUserResponseMessageType extends EWSType
{
    /**
     * Provides a text description of the status of the response.
     *
     * @since Exchnage 2007 SP1
     *
     * @var string
     */
    public $MessageText;

    /**
     * Provides an error code that identifies the specific error that the
     * request encountered.
     *
     * @since Exchnage 2007 SP1
     *
     * @var EWSType_ResponseCodeType
     */
    public $ResponseCode;

    /**
     * Currently unused and is reserved for future use; contains a value of 0.
     *
     * @since Exchnage 2007 SP1
     *
     * @var integer
     */
    public $DescriptiveLinkKey;

    /**
     * Provides additional error response information.
     *
     * @since Exchnage 2007 SP1
     *
     * @var string
     */
    public $MessageXml;

    /**
     * Identifies a single delegate that is returned in a delegate management
     * response.
     *
     * @since Exchnage 2007 SP1
     *
     * @var EWSType_DelegateUserType
     */
    public $DelegateUser;
}
