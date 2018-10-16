<?php

namespace DpSys;

final class License
{
    /**
     * @var string
     */
    private $raw_code;

    /**
     * @var \DpSys\License
     */
    private static $inst;

    /**
     * @var callable
     */
    private static $lic_loader;

    /**
     * @var string
     */
    private $license_id;

    /**
     * @var string
     */
    private $license_salt;

    /**
     * @var string
     */
    private $install_key;

    /**
     * @var array
     */
    private $data = [];

    /**
     * When non-null, then it means there was a problem with the license (ie bad format).
     * The License class goes into unlicensed mode in these cases, but if there was
     * a license code but it was just invalid, then you can always check this.
     *
     * @var string
     */
    private $error_code = null;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var bool
     */
    private $user_copyright_done = false;

    /**
     * @static
     *
     * @return string
     *
     * @deprecated Use getSecureLicServer
     */
    public static function getLicServer()
    {
        if (!defined('DP_MA_SERVER')) {
            define('DP_MA_SERVER', 'http://www.deskpro.com/members');
        }

        return DP_MA_SERVER;
    }

    /**
     * @return string
     */
    public static function getSecureLicServer()
    {
        if (!defined('DP_MA_SERVER_SECURE')) {
            define('DP_MA_SERVER_SECURE', 'https://www.deskpro.com/members');
        }

        return DP_MA_SERVER_SECURE;
    }

    /**
     * @static
     *
     * @return string
     */
    public static function getSupportUrl()
    {
        if (!defined('DP_SUPPORT_URL')) {
            define('DP_SUPPORT_URL', 'https://support.deskpro.com');
        }

        return DP_SUPPORT_URL;
    }

    /**
     * @static
     *
     * @param $license_code
     *
     * @return \DpSys\License
     */
    public static function create($license_code, $install_key = '')
    {
        self::getLicServer();
        self::getSecureLicServer();

        $inst = new self($license_code, $install_key);

        return $inst;
    }

    /**
     * @return \DpSys\License
     */
    public static function setLoaderFunction($fn)
    {
        self::$lic_loader = $fn;
    }

    /**
     * @static
     *
     * @return \DpSys\License
     */
    public static function getLicense()
    {
        if (!self::$inst) {
            if (self::$lic_loader) {
                // a loader is generally set during a kernel event
                // that loads the lic from a license file or the license setting
                $info         = call_user_func(self::$lic_loader);
                $license_code = $info['license_code'];
                $install_key  = $info['install_key'];
                self::$inst   = self::create($license_code, $install_key);
            } else {
                // this will cause an invalid license, but without a loader that is desirable
                self::$inst = self::create('', null);
            }
        }

        return self::$inst;
    }

    /**
     * $license_code is a combined string in the form of:.
     *
     *     <license id><license salt><encrypted license code>
     *
     * The license id is like: ASDD-2000-GGHF (14 chars)
     * The license salt is like: JKHNNSDSD90809SJHDJK (20 chars)
     * The encrypted bit is a base64 encoded string (remaining)
     *
     * @param $license_code
     */
    private function __construct($license_code, $install_key = '')
    {
        // "no license" mode
        if ($license_code === null || $license_code === false || trim($license_code) === '') {
            $this->data = ['no_license' => true];

            return;
        }

        if ($license_code && strlen($license_code) < 300) {
            $this->error_code = 'invalid_license_code_1';
            $this->data       = ['no_license' => true];

            $fn = function () use ($license_code, $install_key) {
                $__license_code = $license_code;
                $__install_key  = $install_key;
                $__fail_message = 'invalid_license_code_1';

                if (php_sapi_name() !== 'cli') {
                    @header('HTTP/1.0 520 Unknown Error');
                    @header('Content-Type: text/plain');
                }
                echo "Invalid license.\n";
                echo "(Code:invalid_license_code_1)\n";
                exit;
            };
            $fn();

            return;
        }

        $license_code   = trim($license_code);
        $license_code   = str_replace(["\n", "\r", ' ', "\t"], '', $license_code);
        $this->raw_code = $license_code;

        $this->install_key = $install_key;
        if (preg_match('#@([A-Z0-9\-_]+)$#', $license_code, $m)) {
            $this->install_key = $m[1];
            $license_code      = str_replace($m[0], '', $license_code);
        }

        // legacy : unused
        $opts = null;
        if (strpos($license_code, '#') !== false) {
            $parts        = explode('#', $license_code, 2);
            $license_code = $parts[0];

            $opts = explode(',', $parts[1]);
        }

        $license_code = base64_decode($license_code);

        $this->license_id   = substr($license_code, 0, 14);
        $this->license_id   = rtrim($this->license_id, '-');
        $this->license_salt = substr($license_code, 14, 20);
        $enc                = substr($license_code, 34);
        $enc                = strrev($enc);

        $key = sha1($this->license_id.$this->license_salt.$this->install_key.'5hIT4WRxHRDP70afPyBwph3wMeAGOVK69zIL62zcS').'7ucrx3ghJwt7m3MNwvhXcddAskF0tLTMpIU3GMK6X';
        $key .= sha1($this->license_id.$this->license_salt.$this->install_key.'aPRfHzg1EHDXtQdXYOlRGrvKJmP7G0UPo4SmLIqt4').'djqhyJa40ucOWDGhQ3taSppI8D5Gpyeoc9BlcIlYv';
        $key = $key.strrev($key);

        $enc = $this->xorString($enc, $key);

        $enc  = base64_decode($enc);
        $data = @unserialize($enc);

        $this->data = $data;

        if (!$data) {
            $this->error_code = 'invalid_license_code_2';
            $this->data       = ['no_license' => true];

            $fn = function () use ($license_code, $install_key) {
                $__license_code = $license_code;
                $__install_key  = $install_key;
                $__fail_message = 'invalid_license_code_2';

                if (php_sapi_name() !== 'cli') {
                    @header('HTTP/1.0 520 Unknown Error');
                    @header('Content-Type: text/plain');
                }

                echo "Invalid license.\n";
                echo "(Code:invalid_license_code_2)\n";
                exit;
            };
            $fn();

            return;
        }

        if ($this->isCloud()) {
            $this->data['agents'] = \DPC_AGENTS;
            if (defined('DPC_DEMO_EXPIRE') && \DPC_DEMO_EXPIRE) {
                $this->data['demo']   = true;
                $this->data['expire'] = \DPC_DEMO_EXPIRE;
            } else {
                $this->data['demo']   = false;
                $this->data['expire'] = \DPC_BILL_DATE;
            }

            if (defined('DPC_COPYFREE') && DPC_COPYFREE) {
                $this->data['copyfree'] = true;
            }
        }

        if (!empty($this->data['lic_flags'])) {
            $this->data['lic_flags'] = explode(',', $this->data['lic_flags']);
            foreach ($this->data['lic_flags'] as $flag) {
                $flag                 = trim($flag);
                $this->options[$flag] = true;
            }
        }

        if (isset(self::$sysdata['xlic'][$this->getLicenseId()])) {
            $this->options['xlic'] = true;
        }

        if (isset($this->options['die'])) {
            echo '(#GRNyVvJL3iUOcpqVgkzQ43qGLgnTfSM4QNe0pCPr)';
            die(1);
        }

        if (isset($this->options['disable_callhome'])) {
            $GLOBALS['DP_DISABLE_SENDREPORTS'] = true;
        }
    }

    /**
     * @return bool
     */
    public function getIsUnlimited()
    {
        if (isset(self::$sysdata['unl_lic'][$this->license_id])) {
            return true;
        }
    }

    public function getLicenseCode()
    {
        return $this->raw_code;
    }

    public function getLicenseId()
    {
        return $this->license_id;
    }

    public function getPublicLicenseRef()
    {
        if (!$this->license_id) {
            return 'NOLIC';
        }

        return md5($this->license_id.'deskpro');
    }

    public function isDemo()
    {
        return isset($this->data['demo']) && $this->data['demo'];
    }

    public function isCloud()
    {
        return defined('DPC_IS_CLOUD');
    }

    public function isCopyfree()
    {
        return isset($this->data['copyfree']) && $this->data['copyfree'];
    }

    public function getMaxAgents()
    {
        if (!isset($this->data['agents']) || !$this->data['agents']) {
            return 0;
        }

        if (!$this->isCloud() && $this->data['agents'] >= 100) {
            return 0;
        }

        return $this->data['agents'];
    }

    public function getExpireDate()
    {
        static $d = null;

        if ($d === null) {
            if (isset($this->data['expire'])) {
                $d = $this->data['expire'] ? new \DateTime('@'.$this->data['expire']) : -1;

                // License has been x'd
                if (isset($this->options['xlic'])) {
                    $d2 = new \DateTime('@'.$this->options['xlic']);
                    $d2->modify('+1 day');

                    if ($d2 < $d) {
                        $d = $d2;
                    }
                }
            } else {
                $d = -1;
            }
        }

        if ($d === -1) {
            return;
        }

        return $d;
    }

    public function getExpireDays()
    {
        return $this->getExpireTime('days');
    }

    public function getExpireTime($unit)
    {
        $d = $this->getExpireDate();
        if (!$d) {
            return;
        }

        switch ($unit) {
            case 'days':
                $diff = $d->diff(new \DateTime());
                // round up if more than 1 day + > 0 hours left
                return $diff->days + (($diff->days && $diff->h) ? 1 : 0);
            case 'hours':
                $diff = $d->diff(new \DateTime());

                return $diff->h + $diff->days * 24;
            case 'mins':
                $diff = $d->diff(new \DateTime());

                return $diff->i + $diff->days * 24 * 60 + $diff->h * 60;
        }

        return 0;
    }

    public function isPastExpireDate()
    {
        $date = $this->getExpireDate();
        if (!$date) {
            return false;
        }

        $now = new \DateTime();
        if ($now > $date) {
            $diff = $now->diff($date);
            $days = max(1, $diff->d);

            return $days;
        }

        return false;
    }

    public function hasLicense()
    {
        return !isset($this->data['no_license']);
    }

    public function isLicenseCodeError()
    {
        return $this->error_code !== null;
    }

    public function getLicenseCodeError()
    {
        return $this->error_code;
    }

    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasFlag($id)
    {
        return isset($this->options[$id]);
    }

    public static function staticGetUserCopyrightHtml()
    {
        return self::getLicense()->getUserCopyrightHtml();
    }

    public function getUserCopyrightHtml()
    {
        $this->user_copyright_done = true;
        if ($this->isCopyfree()) {
            return '';
        }

        $powered_by_deskpro = 'Helpdesk software by <strong>DeskPRO</strong>';

        $html = <<<STR
<!-- DeskPRO Copyright -->
<div class="dp-copy">
    <a href="http://www.deskpro.com/">$powered_by_deskpro</a>
</div>
<!-- DeskPRO Copyright -->
STR;

        return $html;
    }

    public function hasUserCopyrightHtml($check_source = null)
    {
        if ($this->isCopyfree()) {
            return true;
        }

        if ($check_source) {
            if ($this->user_copyright_done && strpos($check_source, 'dp-copy') !== false) {
                return true;
            } else {
                return false;
            }
        }

        return $this->user_copyright_done;
    }

    private function xorString($string, $key)
    {
        $string_len = strlen($string);
        $key_len    = strlen($key);
        $new_string = [];

        for ($i = 0, $j = 0; $i < $string_len; $i++, $j++) {
            if ($j >= $key_len) {
                $j = 0;
            }

            $new_string[] = chr(ord($string[$i]) ^ ord($key[$j]));
        }

        $new_string = implode('', $new_string);

        return $new_string;
    }

    private static $sysdata = [
        'xlic' => [
            'KDQP-8287-VSWH' => true,
            'JPPJ-8339-DIFJ' => true,
        ],

        'unl_lic' => [
            'QVMO-3549-CFYS' => true,
            'WGYX-1723-WXBX' => true,
            'EGTW-8743-ASJQ' => true,
            'JFZU-5462-AWJF' => true,
            'DWRF-0120-XVYB' => true,
            'UJMN-6712-TOPN' => true,
            'ZJWJ-0784-FWAU' => true,
            'BRDI-8207-QQYC' => true,
            'ZIGH-0841-BYTN' => true,
            'URTJ-5584-BIPR' => true,
            'CJHK-0609-VGZA' => true,
            'SQYZ-2993-XAXY' => true,
            'RMUK-9748-FFWM' => true,
            'SBNB-3949-DUHF' => true,
            'PXCA-5940-XBRC' => true,
            'FIDS-7156-DVXK' => true,
            'RSYT-6143-WGNM' => true,
            'OIGW-1146-WQCU' => true,
            'RQXZ-2692-SZZC' => true,
            'PDJK-9034-ZKOX' => true,
            'QDYP-7460-HSZQ' => true,
            'TWMJ-4993-TOVP' => true,
        ],
    ];
}
