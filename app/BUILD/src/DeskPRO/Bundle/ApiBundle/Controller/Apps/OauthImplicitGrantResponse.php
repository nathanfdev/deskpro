<?php namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use Symfony\Component\HttpFoundation\Response;

class OauthImplicitGrantResponse extends Response
{

    /**
     * OauthImplicitGrantResponse constructor.
     */
    public function __construct($status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);
        $content = <<<EOT
<html>
<body>
<script>
  if (window.opener != null && !window.opener.closed) {
    window.opener.postMessage({
        type:       "oauth-proxy-callback",
        status:     "success",
        body: {
            hash:       window.location.hash  
        }     
    }, window.location.origin);
  }
  window.close();
</script>
</body>
</html>

EOT;
        $this->setContent($content);
        $this->headers->add(['Content-Type' =>  'text/html; charset=UTF8']);
    }
}
