This is DeskPRO's version of the facebook/php-sdk 3.2.

Facebook's 3.2 SDK calls a sesison_start() and deals with cookies and session direclty within its "Facebook"
object, and that is troublesome for us. When authenticating it can cause disruptions in our own session and 
result in making the user unable to login. 

The version we replaced it has been edited to use our own \Orb\Auth\StateHandler\StateHandlerInterface
as its state storage between requests.

We have moved facebook sdk to "vendor-src" and removed it from composer.json