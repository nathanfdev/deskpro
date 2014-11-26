<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Validator\Constraints;


use Application\DeskPRO\Brand\BrandStack;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

class ValidCaptchaValidator extends ConstraintValidator
{
    protected $cache;

    protected $private_key;
    /**
     * Request Stack
     *
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $request_stack;
    /**
     * The reCAPTCHA server URL's
     */
    const RECAPTCHA_VERIFY_SERVER = 'www.google.com';
    /**
     * @var \Application\DeskPRO\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @param BrandStack   $brand_stack
     * @param RequestStack $request_stack
     */
    public function __construct(BrandStack $brand_stack, RequestStack $request_stack)
    {
        $this->private_key = $brand_stack->getActive()->getSetting('core.recaptcha_private_key');
        $this->request_stack = $request_stack;
        $this->brand_stack = $brand_stack;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        // define variable for recaptcha check answer
        $remoteip = $this->request_stack->getMasterRequest()->server->get('REMOTE_ADDR');
        $challenge = $this->request_stack->getMasterRequest()->get('recaptcha_challenge_field');
        $response = $this->request_stack->getMasterRequest()->get('recaptcha_response_field');
        if (
            isset($this->cache[$this->private_key]) &&
            isset($this->cache[$this->private_key][$remoteip]) &&
            isset($this->cache[$this->private_key][$remoteip][$challenge]) &&
            isset($this->cache[$this->private_key][$remoteip][$challenge][$response])
        ) {
            $cached = $this->cache[$this->private_key][$remoteip][$challenge][$response];
        } else {
            $cached = $this->cache[$this->private_key][$remoteip][$challenge][$response] = $this->checkAnswer($this->private_key, $remoteip, $challenge, $response);
        }
        if (!$cached) {
            $this->context->addViolation($constraint->message);
        }
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @param string $privateKey
     * @param string $remoteip
     * @param string $challenge
     * @param string $response
     * @param array  $extra_params an array of extra variables to post to the server
     *
     * @throws ValidatorException When missing remote ip
     *
     * @return Boolean
     */
    private function checkAnswer($privateKey, $remoteip, $challenge, $response, $extra_params = array())
    {
        if ($remoteip == null || $remoteip == '') {
            throw new ValidatorException('For security reasons, you must pass the remote ip to reCAPTCHA');
        }
        // discard spam submissions
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
            return false;
        }
        $response = $this->httpPost(self::RECAPTCHA_VERIFY_SERVER, '/recaptcha/api/verify', array(
                'privatekey' => $privateKey,
                'remoteip'   => $remoteip,
                'challenge'  => $challenge,
                'response'   => $response
            ) + $extra_params);
        $answers = explode("\n", $response [1]);
        if (trim($answers[0]) == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server
     *
     * @param string $host
     * @param string $path
     * @param array  $data
     * @param        int port
     *
     * @return array response
     */
    private function httpPost($host, $path, $data, $port = 80)
    {
        $req = $this->getQSEncode($data);
        $http_request = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: ".strlen($req)."\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;
        $response = null;
        if (!$fs = @fsockopen($host, $port, $errno, $errstr, 10)) {
            throw new ValidatorException('Could not open socket');
        }
        fwrite($fs, $http_request);
        while (!feof($fs)) {
            $response .= fgets($fs, 1160); // one TCP-IP packet
        }
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
    }

    /**
     * Encodes the given data into a query string format
     *
     * @param $data - array of string elements to be encoded
     *
     * @return string - encoded request
     */
    private function getQSEncode($data)
    {
        $req = null;
        foreach ($data as $key => $value) {
            $req .= $key.'='.urlencode(stripslashes($value)).'&';
        }
        // cut the last '&'
        $req = substr($req, 0, strlen($req) - 1);

        return $req;
    }
}
 