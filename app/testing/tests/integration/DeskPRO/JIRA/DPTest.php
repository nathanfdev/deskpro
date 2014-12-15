<?php

namespace DpIntegrationTests\DeskPRO\JIRA;

use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\JIRA\WebhookHandler;
use Application\DeskPRO\Service\JIRA;
use Doctrine\ORM\EntityRepository;
use DpFixtures\JIRA\TriggerData;

class APITest extends \DpIntegrationTestCase
{
    /**
     * @var EntityRepository
     */
    protected $rep;

    /**
     * @var JIRA
     */
    protected $service;

    protected $data;

    public function runBefore()
    {
        $this->helper->enableDestructiveDatabaseSet('ApiSampleDb');
        $this->helper->loadFixtures('JIRA/AppData');
        $this->helper->loadFixtures('JIRA/TriggerData');

        $this->data = array(
            'duedate' => new \DateTime('+1 week'),
            'user' => 'test-user',
            'summary' => 'Auto test issue',
            'url' => 'http://example.com',
            'ticket_global_id' => 'deskpro_ticket_test',

            'webhook' => array(
                'issue_updated_1' => '{"timestamp":1418662115412,"webhookEvent":"jira:issue_updated","user":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"issue":{"id":"%d","self":"https://deskpro.atlassian.net/rest/api/2/issue/11118","key":"SAN-135","fields":{"issuetype":{"self":"https://deskpro.atlassian.net/rest/api/2/issuetype/10100","id":"10100","description":"","iconUrl":"https://deskpro.atlassian.net/secure/viewavatar?size=xsmall&avatarId=10300&avatarType=issuetype","name":"My Issue Type","subtask":false,"avatarId":10300},"timespent":null,"project":{"self":"https://deskpro.atlassian.net/rest/api/2/project/10200","id":"10200","key":"SAN","name":"Sandbox","avatarUrls":{"48x48":"https://deskpro.atlassian.net/secure/projectavatar?pid=10200&avatarId=10011","24x24":"https://deskpro.atlassian.net/secure/projectavatar?size=small&pid=10200&avatarId=10011","16x16":"https://deskpro.atlassian.net/secure/projectavatar?size=xsmall&pid=10200&avatarId=10011","32x32":"https://deskpro.atlassian.net/secure/projectavatar?size=medium&pid=10200&avatarId=10011"}},"customfield_10110":null,"customfield_10111":null,"aggregatetimespent":null,"resolution":null,"customfield_10104":null,"customfield_10105":null,"customfield_10106":null,"customfield_10107":null,"customfield_10108":null,"customfield_10109":null,"resolutiondate":"2014-12-15T16:00:34.318+0000","workratio":-1,"lastViewed":"2014-12-15T16:04:27.104+0000","watches":{"self":"https://deskpro.atlassian.net/rest/api/2/issue/SAN-135/watchers","watchCount":1,"isWatching":true},"created":"2014-12-15T16:00:34.328+0000","customfield_10020":null,"customfield_10021":"Not Started","customfield_10022":null,"priority":{"self":"https://deskpro.atlassian.net/rest/api/2/priority/3","iconUrl":"https://deskpro.atlassian.net/images/icons/priorities/major.png","name":"Major","id":"3"},"customfield_10100":null,"customfield_10101":null,"customfield_10102":null,"labels":[],"customfield_10103":null,"customfield_10016":null,"customfield_10017":null,"customfield_10018":null,"customfield_10019":null,"timeestimate":null,"aggregatetimeoriginalestimate":null,"issuelinks":[],"assignee":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"updated":"2014-12-15T16:04:24.733+0000","status":{"self":"https://deskpro.atlassian.net/rest/api/2/status/3","description":"This issue is being actively worked on at the moment by the assignee.","iconUrl":"https://deskpro.atlassian.net/images/icons/statuses/inprogress.png","name":"In Progress","id":"3","statusCategory":{"self":"https://deskpro.atlassian.net/rest/api/2/statuscategory/4","id":4,"key":"indeterminate","colorName":"yellow","name":"In Progress"}},"timeoriginalestimate":null,"description":null,"customfield_10011":"0|1000pg:","customfield_10014":null,"customfield_10015":null,"customfield_10006":null,"customfield_10007":null,"attachment":[],"aggregatetimeestimate":null,"summary":"Auto test issue","creator":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"subtasks":[],"reporter":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"customfield_10000":null,"aggregateprogress":{"progress":0,"total":0},"customfield_10001":"2014-12-15 16:48:35.4","customfield_10003":null,"environment":null,"duedate":"2014-12-22","progress":{"progress":0,"total":0},"comment":{"startAt":0,"maxResults":2,"total":2,"comments":[{"self":"https://deskpro.atlassian.net/rest/api/2/issue/11118/comment/11113","id":"11113","author":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"body":"[Admin Admin via DeskPRO #1|http://localhost:8888/agent/#app.tickets,t.o:1]: test comment message","updateAuthor":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"created":"2014-12-15T16:00:36.780+0000","updated":"2014-12-15T16:00:36.780+0000"},{"self":"https://deskpro.atlassian.net/rest/api/2/issue/11118/comment/11129","id":"11129","author":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"body":"new comment","updateAuthor":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"created":"2014-12-15T16:48:35.400+0000","updated":"2014-12-15T16:48:35.400+0000"}]},"worklog":{"startAt":0,"maxResults":20,"total":0,"worklogs":[]}}},"comment":{"self":"https://deskpro.atlassian.net/rest/api/2/issue/11118/comment/11129","id":"11129","author":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"body":"new comment","updateAuthor":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"created":"2014-12-15T16:48:35.400+0000","updated":"2014-12-15T16:48:35.400+0000"}}',
                'issue_updated_2' => '{"timestamp":1418659464740,"webhookEvent":"jira:issue_updated","user":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"issue":{"id":"%d","self":"https://deskpro.atlassian.net/rest/api/2/issue/11118","key":"SAN-135","fields":{"issuetype":{"self":"https://deskpro.atlassian.net/rest/api/2/issuetype/10100","id":"10100","description":"","iconUrl":"https://deskpro.atlassian.net/secure/viewavatar?size=xsmall&avatarId=10300&avatarType=issuetype","name":"My Issue Type","subtask":false,"avatarId":10300},"timespent":null,"project":{"self":"https://deskpro.atlassian.net/rest/api/2/project/10200","id":"10200","key":"SAN","name":"Sandbox","avatarUrls":{"48x48":"https://deskpro.atlassian.net/secure/projectavatar?pid=10200&avatarId=10011","24x24":"https://deskpro.atlassian.net/secure/projectavatar?size=small&pid=10200&avatarId=10011","16x16":"https://deskpro.atlassian.net/secure/projectavatar?size=xsmall&pid=10200&avatarId=10011","32x32":"https://deskpro.atlassian.net/secure/projectavatar?size=medium&pid=10200&avatarId=10011"}},"customfield_10110":null,"customfield_10111":null,"aggregatetimespent":null,"resolution":null,"customfield_10104":null,"customfield_10105":null,"customfield_10106":null,"customfield_10107":null,"customfield_10108":null,"customfield_10109":null,"resolutiondate":"2014-12-15T16:00:34.318+0000","workratio":-1,"lastViewed":"2014-12-15T16:04:24.704+0000","watches":{"self":"https://deskpro.atlassian.net/rest/api/2/issue/SAN-135/watchers","watchCount":1,"isWatching":false},"created":"2014-12-15T16:00:34.328+0000","customfield_10020":null,"customfield_10021":"Not Started","customfield_10022":null,"priority":{"self":"https://deskpro.atlassian.net/rest/api/2/priority/3","iconUrl":"https://deskpro.atlassian.net/images/icons/priorities/major.png","name":"Major","id":"3"},"customfield_10100":null,"customfield_10101":null,"customfield_10102":null,"labels":[],"customfield_10103":null,"customfield_10016":null,"customfield_10017":null,"customfield_10018":null,"customfield_10019":null,"timeestimate":null,"aggregatetimeoriginalestimate":null,"issuelinks":[],"assignee":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"updated":"2014-12-15T16:04:24.733+0000","status":{"self":"https://deskpro.atlassian.net/rest/api/2/status/3","description":"This issue is being actively worked on at the moment by the assignee.","iconUrl":"https://deskpro.atlassian.net/images/icons/statuses/inprogress.png","name":"In Progress","id":"3","statusCategory":{"self":"https://deskpro.atlassian.net/rest/api/2/statuscategory/4","id":4,"key":"indeterminate","colorName":"yellow","name":"In Progress"}},"timeoriginalestimate":null,"description":null,"customfield_10011":"0|1000pg:","customfield_10014":null,"customfield_10015":null,"customfield_10006":null,"customfield_10007":null,"attachment":[],"aggregatetimeestimate":null,"summary":"Auto test issue","creator":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"subtasks":[],"reporter":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"customfield_10000":null,"aggregateprogress":{"progress":0,"total":0},"customfield_10001":null,"customfield_10003":null,"environment":null,"duedate":"2014-12-22","progress":{"progress":0,"total":0},"comment":{"startAt":0,"maxResults":1,"total":1,"comments":[{"self":"https://deskpro.atlassian.net/rest/api/2/issue/11118/comment/11113","id":"11113","author":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"body":"[Admin Admin via DeskPRO #1|http://localhost:8888/agent/#app.tickets,t.o:1]: test comment message","updateAuthor":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"created":"2014-12-15T16:00:36.780+0000","updated":"2014-12-15T16:00:36.780+0000"}]},"worklog":{"startAt":0,"maxResults":20,"total":0,"worklogs":[]}}},"changelog":{"id":"11227","items":[{"field":"status","fieldtype":"jira","from":"10001","fromString":"To Do","to":"3","toString":"In Progress"},{"field":"assignee","fieldtype":"jira","from":null,"fromString":null,"to":"n3b","toString":"Maxim"}]}}',
                'issue_deleted' => '{"timestamp":1418656993498,"webhookEvent":"jira:issue_deleted","user":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"issue":{"id":"%d","self":"https://deskpro.atlassian.net/rest/api/2/issue/11114","key":"SAN-131","fields":{"issuetype":{"self":"https://deskpro.atlassian.net/rest/api/2/issuetype/10100","id":"10100","description":"","iconUrl":"https://deskpro.atlassian.net/secure/viewavatar?size=xsmall&avatarId=10300&avatarType=issuetype","name":"My Issue Type","subtask":false,"avatarId":10300},"timespent":null,"project":{"self":"https://deskpro.atlassian.net/rest/api/2/project/10200","id":"10200","key":"SAN","name":"Sandbox","avatarUrls":{"48x48":"https://deskpro.atlassian.net/secure/projectavatar?pid=10200&avatarId=10011","24x24":"https://deskpro.atlassian.net/secure/projectavatar?size=small&pid=10200&avatarId=10011","16x16":"https://deskpro.atlassian.net/secure/projectavatar?size=xsmall&pid=10200&avatarId=10011","32x32":"https://deskpro.atlassian.net/secure/projectavatar?size=medium&pid=10200&avatarId=10011"}},"customfield_10110":null,"customfield_10111":null,"aggregatetimespent":null,"resolution":null,"customfield_10104":null,"customfield_10105":null,"customfield_10106":null,"customfield_10107":null,"customfield_10108":null,"customfield_10109":null,"resolutiondate":"2014-12-15T15:23:08.494+0000","workratio":-1,"lastViewed":null,"watches":{"self":"https://deskpro.atlassian.net/rest/api/2/issue/SAN-131/watchers","watchCount":1,"isWatching":true},"created":"2014-12-15T15:23:08.507+0000","customfield_10020":null,"customfield_10021":"Not Started","customfield_10022":null,"priority":{"self":"https://deskpro.atlassian.net/rest/api/2/priority/3","iconUrl":"https://deskpro.atlassian.net/images/icons/priorities/major.png","name":"Major","id":"3"},"customfield_10100":null,"customfield_10101":null,"customfield_10102":null,"labels":[],"customfield_10103":null,"customfield_10016":null,"customfield_10017":null,"customfield_10018":null,"customfield_10019":null,"timeestimate":null,"aggregatetimeoriginalestimate":null,"issuelinks":[],"assignee":null,"updated":"2014-12-15T15:23:13.221+0000","status":{"self":"https://deskpro.atlassian.net/rest/api/2/status/10001","description":"","iconUrl":"https://deskpro.atlassian.net/images/icons/statuses/open.png","name":"To Do","id":"10001","statusCategory":{"self":"https://deskpro.atlassian.net/rest/api/2/statuscategory/2","id":2,"key":"new","colorName":"blue-gray","name":"To Do"}},"timeoriginalestimate":null,"description":null,"customfield_10011":"0|1000os:","customfield_10014":null,"customfield_10015":null,"customfield_10006":null,"customfield_10007":null,"attachment":[],"aggregatetimeestimate":null,"summary":"Auto test issue","creator":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"subtasks":[],"reporter":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"customfield_10000":null,"aggregateprogress":{"progress":0,"total":0},"customfield_10001":null,"customfield_10003":null,"environment":null,"duedate":"2014-12-22","progress":{"progress":0,"total":0},"comment":{"startAt":0,"maxResults":1,"total":1,"comments":[{"self":"https://deskpro.atlassian.net/rest/api/2/issue/11114/comment/11109","id":"11109","author":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"body":"[Admin Admin via DeskPRO #1|http://localhost:8888/agent/#app.tickets,t.o:1]: test comment message","updateAuthor":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=test-user","name":"test-user","key":"test-user","emailAddress":"maxim.vorobey+jira-test@deskpro.com","avatarUrls":{"48x48":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=48","24x24":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=24","16x16":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=16","32x32":"https://secure.gravatar.com/avatar/058aa58f94e14b064645584f74e7e942?d=mm&s=32"},"displayName":"Test User","active":true,"timeZone":"Europe/London"},"created":"2014-12-15T15:23:12.127+0000","updated":"2014-12-15T15:23:12.127+0000"}]},"worklog":{"startAt":0,"maxResults":20,"total":0,"worklogs":[]}}}}',
            ),
        );
    }

    /**
     * @return JIRA
     */
    protected function js()
    {
        if (!$this->service) {
            $this->service = $this->helper->getSymfonyContainer()->get(JIRA::NAME);
        }
        return $this->service;
    }

    public function testJIRAFunctions()
    {
        $em = $this->helper->getSymfonyContainer()->getEm();
        $service = $this->js();
        $api = $service->getApi();
        $service->updateMeta(array('default_fields_summary' => array('comment', 'project', 'issuetype', 'summary')));
        /** @var Ticket $ticket */
        $ticket = $em->find('DeskPRO:Ticket', 1);
        /** @var Person $person */
        $person = $em->find('DeskPRO:Person', 1);

        $issue = array(
            'fields' => array(
                'duedate' => $this->data['duedate']->format('Y-m-d'),
                'issuetype' => array('id' => 10100),
                'project' => array('id' => 10200),
                'summary' => $this->data['summary'],
            ),
        );

        $response = $service->createIssueJson(json_encode($issue));
        $this->assertArrayHasKey('id', $response);
        $service->link($ticket, $response['id'], $person);

        /** @var JiraIssue $issue */
        $issue = $em->getRepository('DeskPRO:JiraIssue')->findOneBy(array());
        $this->assertInstanceOf('Application\DeskPRO\Entity\JiraIssue', $issue);
        $this->assertEquals($ticket, $issue->ticket);

        $service->addComment('test comment message', $ticket['id'], $person, $issue['issue_id']);

        // test get ticket issues list
        $issues = $service->issues($ticket['id']);
        $this->assertArrayHasKey('issues', $issues);
        $this->assertArrayHasKey('total', $issues);
        $this->assertEquals(1, $issues['total']);

        $issueData = $issues['issues'][0];
        $this->assertEquals($issue['issue_id'], $issueData['id']);
        $this->assertArrayHasKey('fields', $issueData);
        $this->assertArrayHasKey('comment', $issueData['fields']);
        $this->assertArrayHasKey('comments', $issueData['fields']['comment']);
        $this->assertCount(2, $issueData['fields']['comment']['comments']);

        $commentByTrigger = $issueData['fields']['comment']['comments'][0];
        $commentByAPI = $issueData['fields']['comment']['comments'][1];

        // test comments
        $this->assertContains(TriggerData::NEW_ISSUE_COMMENT, $commentByTrigger['body']);
        $body = $commentByAPI['body'];
        $pattern = '/^\[(' . $person->getDisplayName() . ') via DeskPRO \#(' . $ticket['id'] . ')[^\]]+\]\: (test comment message)$/';
        $this->assertEquals(1, preg_match_all($pattern, $body, $matches));

        // check comment trigger term
        $this->assertCount(1, $ticket->labels);
        $this->assertEquals(TriggerData::LABEL_COMMENT, $ticket->labels[0]['label']);

        // cleanup
        $this->assertTrue($service->removeRemoteIssueLink($issue));
        $api->delete('/issue/' . $issue['issue_id']);
    }

    public function testWebhookHandler()
    {
        $em = $this->helper->getSymfonyContainer()->getEm();
        $service = $this->js();
        $api = $service->getApi();

        /** @var Ticket $ticket */
        $ticket = $em->find('DeskPRO:Ticket', 1);
        /** @var Person $person */
        $person = $em->find('DeskPRO:Person', 1);

        $issue = array(
            'fields' => array(
                'duedate' => $this->data['duedate']->format('Y-m-d'),
                'issuetype' => array('id' => 10100),
                'project' => array('id' => 10200),
                'summary' => $this->data['summary'],
            ),
        );
        $response = $this->js()->createIssueJson(json_encode($issue));
        $service->link($ticket, $response['id'], $person);
        $issue = $em->getRepository('DeskPRO:JiraIssue')->findOneBy(array());


        // test webhooks
        $handler = new WebhookHandler($this->helper->getSymfonyContainer());
        foreach ($this->data['webhook'] as $data) {
            $data = json_decode(sprintf($data, $response['id']), 1);
            $this->assertArrayHasKey('webhookEvent', $data);
            $this->assertTrue($handler->handle($data));
        }

        foreach ($ticket->labels as $label) {
            $res[] = $label['label'];
        }
        $res = array_flip($res);
        $this->assertArrayHasKey(TriggerData::LABEL_COMMENT, $res);
        $this->assertArrayHasKey(TriggerData::LABEL_STATUS, $res);

        $this->assertTrue($service->removeRemoteIssueLink($issue));
        $api->delete('/issue/' . $issue['issue_id']);
    }
}
