<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
*/

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\Entity\JiraIssue;
use Application\DeskPRO\Service\JIRA;
use Application\DeskPRO\Tickets\ExecutorContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JIRAWebhookController extends AbstractController
{
    /**
     * JIRA webhook endpoint
     * @param Request $request
     * @return Response
     */
	public function handleAction(Request $request)
    {
        $response = new Response();
        $content = $request->getContent();
        // created
        $content = '{"webhookEvent":"jira:issue_created","user":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"issue":{"id":"10614","self":"https://deskpro.atlassian.net/rest/api/2/issue/10614","key":"SAN-51","fields":{"summary":"testing webhook","progress":{"progress":0,"total":0},"issuetype":{"self":"https://deskpro.atlassian.net/rest/api/2/issuetype/10100","id":"10100","description":"","iconUrl":"https://deskpro.atlassian.net/secure/viewavatar?size=xsmall&avatarId=10300&avatarType=issuetype","name":"My Issue Type","subtask":false,"avatarId":10300},"timespent":null,"reporter":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"created":"2014-11-16T00:22:34.504+0000","updated":"2014-11-16T00:22:34.504+0000","priority":{"self":"https://deskpro.atlassian.net/rest/api/2/priority/3","iconUrl":"https://deskpro.atlassian.net/images/icons/priorities/major.png","name":"Major","id":"3"},"description":null,"customfield_10001":null,"customfield_10003":null,"issuelinks":[],"customfield_10000":null,"subtasks":[],"customfield_10007":null,"status":{"self":"https://deskpro.atlassian.net/rest/api/2/status/10001","description":"","iconUrl":"https://deskpro.atlassian.net/images/icons/statuses/open.png","name":"To Do","id":"10001","statusCategory":{"self":"https://deskpro.atlassian.net/rest/api/2/statuscategory/2","id":2,"key":"new","colorName":"blue-gray","name":"To Do"}},"customfield_10006":null,"labels":[],"workratio":-1,"project":{"self":"https://deskpro.atlassian.net/rest/api/2/project/10200","id":"10200","key":"SAN","name":"Sandbox","avatarUrls":{"16x16":"https://deskpro.atlassian.net/secure/projectavatar?size=xsmall&pid=10200&avatarId=10011","24x24":"https://deskpro.atlassian.net/secure/projectavatar?size=small&pid=10200&avatarId=10011","32x32":"https://deskpro.atlassian.net/secure/projectavatar?size=medium&pid=10200&avatarId=10011","48x48":"https://deskpro.atlassian.net/secure/projectavatar?pid=10200&avatarId=10011"}},"environment":null,"customfield_10014":null,"aggregateprogress":{"progress":0,"total":0},"lastViewed":null,"customfield_10015":null,"comment":{"startAt":0,"maxResults":0,"total":0,"comments":[]},"timeoriginalestimate":null,"customfield_10011":"0|1000d8:","customfield_10017":null,"customfield_10016":null,"customfield_10111":null,"customfield_10019":null,"customfield_10018":null,"customfield_10110":null,"resolution":{"self":"https://deskpro.atlassian.net/rest/api/2/resolution/1","id":"1","description":"A fix for this issue is checked into the tree and tested.","name":"Fixed"},"resolutiondate":"2014-11-16T00:22:34.493+0000","creator":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"aggregatetimeoriginalestimate":null,"duedate":"2014-11-06","customfield_10108":null,"customfield_10020":null,"customfield_10109":null,"customfield_10021":"Not Started","customfield_10022":null,"customfield_10104":null,"watches":{"self":"https://deskpro.atlassian.net/rest/api/2/issue/SAN-51/watchers","watchCount":0,"isWatching":false},"customfield_10105":null,"worklog":{"startAt":0,"maxResults":20,"total":0,"worklogs":[]},"customfield_10106":null,"customfield_10107":null,"customfield_10103":null,"customfield_10102":{"self":"https://deskpro.atlassian.net/rest/api/2/customFieldOption/10106","value":"two","id":"10106"},"customfield_10101":{"self":"https://deskpro.atlassian.net/rest/api/2/customFieldOption/10104","value":"three","id":"10104"},"customfield_10100":[{"self":"https://deskpro.atlassian.net/rest/api/2/customFieldOption/10100","value":"one","id":"10100"},{"self":"https://deskpro.atlassian.net/rest/api/2/customFieldOption/10101","value":"two","id":"10101"}],"assignee":null,"attachment":[],"aggregatetimeestimate":null,"timeestimate":null,"aggregatetimespent":null}},"timestamp":1416097354623}';
        // comment
        $content = '{"webhookEvent":"jira:issue_updated","user":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"issue":{"id":"10613","self":"https://deskpro.atlassian.net/rest/api/2/issue/10613","key":"SAN-50","fields":{"summary":"[Ticket #46] dgdfgdfgdf dfgfdgfdg","progress":{"progress":0,"total":0},"issuetype":{"self":"https://deskpro.atlassian.net/rest/api/2/issuetype/10100","id":"10100","description":"","iconUrl":"https://deskpro.atlassian.net/secure/viewavatar?size=xsmall&avatarId=10300&avatarType=issuetype","name":"My Issue Type","subtask":false,"avatarId":10300},"timespent":null,"reporter":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"created":"2014-11-15T22:58:35.747+0000","updated":"2014-11-16T00:07:26.057+0000","priority":{"self":"https://deskpro.atlassian.net/rest/api/2/priority/3","iconUrl":"https://deskpro.atlassian.net/images/icons/priorities/major.png","name":"Major","id":"3"},"description":null,"customfield_10001":null,"customfield_10003":null,"issuelinks":[],"customfield_10000":null,"subtasks":[],"customfield_10007":null,"status":{"self":"https://deskpro.atlassian.net/rest/api/2/status/3","description":"This issue is being actively worked on at the moment by the assignee.","iconUrl":"https://deskpro.atlassian.net/images/icons/statuses/inprogress.png","name":"In Progress","id":"3","statusCategory":{"self":"https://deskpro.atlassian.net/rest/api/2/statuscategory/4","id":4,"key":"indeterminate","colorName":"yellow","name":"In Progress"}},"customfield_10006":null,"labels":[],"workratio":-1,"project":{"self":"https://deskpro.atlassian.net/rest/api/2/project/10200","id":"10200","key":"SAN","name":"Sandbox","avatarUrls":{"16x16":"https://deskpro.atlassian.net/secure/projectavatar?size=xsmall&pid=10200&avatarId=10011","24x24":"https://deskpro.atlassian.net/secure/projectavatar?size=small&pid=10200&avatarId=10011","32x32":"https://deskpro.atlassian.net/secure/projectavatar?size=medium&pid=10200&avatarId=10011","48x48":"https://deskpro.atlassian.net/secure/projectavatar?pid=10200&avatarId=10011"}},"environment":null,"customfield_10014":null,"aggregateprogress":{"progress":0,"total":0},"lastViewed":"2014-11-16T00:07:26.041+0000","customfield_10015":null,"comment":{"startAt":0,"maxResults":1,"total":1,"comments":[{"self":"https://deskpro.atlassian.net/rest/api/2/issue/10613/comment/10621","id":"10621","author":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"body":"webhook comment test","updateAuthor":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"created":"2014-11-16T00:55:52.474+0000","updated":"2014-11-16T00:55:52.474+0000"}]},"timeoriginalestimate":null,"customfield_10011":"0|1000d0:","customfield_10017":null,"customfield_10016":null,"customfield_10111":null,"customfield_10019":null,"customfield_10018":null,"customfield_10110":null,"resolution":{"self":"https://deskpro.atlassian.net/rest/api/2/resolution/3","id":"3","description":"The problem is a duplicate of an existing issue.","name":"Duplicate"},"resolutiondate":"2014-11-15T22:58:35.737+0000","creator":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"aggregatetimeoriginalestimate":null,"duedate":"2014-11-13","customfield_10108":null,"customfield_10020":null,"customfield_10109":null,"customfield_10021":"Not Started","customfield_10022":null,"customfield_10104":null,"watches":{"self":"https://deskpro.atlassian.net/rest/api/2/issue/SAN-50/watchers","watchCount":1,"isWatching":true},"customfield_10105":null,"worklog":{"startAt":0,"maxResults":20,"total":0,"worklogs":[]},"customfield_10106":null,"customfield_10107":null,"customfield_10103":[{"self":"https://deskpro.atlassian.net/rest/api/2/customFieldOption/10109","value":"two","id":"10109"}],"customfield_10102":null,"customfield_10101":null,"customfield_10100":[{"self":"https://deskpro.atlassian.net/rest/api/2/customFieldOption/10101","value":"two","id":"10101"}],"assignee":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"attachment":[],"aggregatetimeestimate":null,"timeestimate":null,"aggregatetimespent":null}},"comment":{"self":"https://deskpro.atlassian.net/rest/api/2/issue/10613/comment/10621","id":"10621","author":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"body":"webhook comment test","updateAuthor":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"created":"2014-11-16T00:55:52.474+0000","updated":"2014-11-16T00:55:52.474+0000"},"timestamp":1416099352477}';
	    // status changed
	    $content = '{"webhookEvent":"jira:issue_updated","user":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"issue":{"id":"10613","self":"https://deskpro.atlassian.net/rest/api/2/issue/10613","key":"SAN-50","fields":{"summary":"[Ticket #46] dgdfgdfgdf dfgfdgfdg","progress":{"progress":0,"total":0},"issuetype":{"self":"https://deskpro.atlassian.net/rest/api/2/issuetype/10100","id":"10100","description":"","iconUrl":"https://deskpro.atlassian.net/secure/viewavatar?size=xsmall&avatarId=10300&avatarType=issuetype","name":"My Issue Type","subtask":false,"avatarId":10300},"timespent":null,"reporter":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"created":"2014-11-15T22:58:35.747+0000","updated":"2014-11-16T17:57:39.646+0000","priority":{"self":"https://deskpro.atlassian.net/rest/api/2/priority/3","iconUrl":"https://deskpro.atlassian.net/images/icons/priorities/major.png","name":"Major","id":"3"},"description":null,"customfield_10001":null,"customfield_10003":null,"issuelinks":[],"customfield_10000":null,"subtasks":[],"customfield_10007":null,"status":{"self":"https://deskpro.atlassian.net/rest/api/2/status/10001","description":"","iconUrl":"https://deskpro.atlassian.net/images/icons/statuses/open.png","name":"To Do","id":"10001","statusCategory":{"self":"https://deskpro.atlassian.net/rest/api/2/statuscategory/2","id":2,"key":"new","colorName":"blue-gray","name":"To Do"}},"customfield_10006":null,"labels":[],"workratio":-1,"project":{"self":"https://deskpro.atlassian.net/rest/api/2/project/10200","id":"10200","key":"SAN","name":"Sandbox","avatarUrls":{"16x16":"https://deskpro.atlassian.net/secure/projectavatar?size=xsmall&pid=10200&avatarId=10011","24x24":"https://deskpro.atlassian.net/secure/projectavatar?size=small&pid=10200&avatarId=10011","32x32":"https://deskpro.atlassian.net/secure/projectavatar?size=medium&pid=10200&avatarId=10011","48x48":"https://deskpro.atlassian.net/secure/projectavatar?pid=10200&avatarId=10011"}},"environment":null,"customfield_10014":null,"aggregateprogress":{"progress":0,"total":0},"lastViewed":"2014-11-16T17:57:39.634+0000","customfield_10015":null,"comment":{"startAt":0,"maxResults":1,"total":1,"comments":[{"self":"https://deskpro.atlassian.net/rest/api/2/issue/10613/comment/10621","id":"10621","author":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"body":"webhook comment test","updateAuthor":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"created":"2014-11-16T00:55:52.474+0000","updated":"2014-11-16T00:55:52.474+0000"}]},"timeoriginalestimate":null,"customfield_10011":"0|1000d0:","customfield_10017":null,"customfield_10016":null,"customfield_10111":null,"customfield_10019":null,"customfield_10018":null,"customfield_10110":null,"resolution":{"self":"https://deskpro.atlassian.net/rest/api/2/resolution/3","id":"3","description":"The problem is a duplicate of an existing issue.","name":"Duplicate"},"resolutiondate":"2014-11-15T22:58:35.737+0000","creator":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"aggregatetimeoriginalestimate":null,"duedate":"2014-11-13","customfield_10108":null,"customfield_10020":null,"customfield_10109":null,"customfield_10021":"Not Started","customfield_10022":null,"customfield_10104":null,"watches":{"self":"https://deskpro.atlassian.net/rest/api/2/issue/SAN-50/watchers","watchCount":1,"isWatching":true},"customfield_10105":null,"worklog":{"startAt":0,"maxResults":20,"total":0,"worklogs":[]},"customfield_10106":null,"customfield_10107":null,"customfield_10103":[{"self":"https://deskpro.atlassian.net/rest/api/2/customFieldOption/10109","value":"two","id":"10109"}],"customfield_10102":null,"customfield_10101":null,"customfield_10100":[{"self":"https://deskpro.atlassian.net/rest/api/2/customFieldOption/10101","value":"two","id":"10101"}],"assignee":{"self":"https://deskpro.atlassian.net/rest/api/2/user?username=n3b","name":"n3b","key":"n3b","emailAddress":"maxim.vorobey@deskpro.com","avatarUrls":{"16x16":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=16","24x24":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=24","32x32":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=32","48x48":"https://secure.gravatar.com/avatar/bac61aacb2cca1ae2fb9542846694ebb?d=mm&s=48"},"displayName":"Maxim","active":true,"timeZone":"Europe/London"},"attachment":[],"aggregatetimeestimate":null,"timeestimate":null,"aggregatetimespent":null}},"changelog":{"id":"10638","items":[{"field":"status","fieldtype":"jira","from":"3","fromString":"In Progress","to":"10001","toString":"To Do"}]},"timestamp":1416160659648}';

	    /** @var JIRA $js */
	    $js = $this->get(JIRA::NAME);
	    if (!$js->isEnabled()) {
		    return $response;
	    }

        if (!$json = json_decode($content, 1)) {
            return $response;
        }

        if (!isset($json['webhookEvent'])) {
            return $response;
        }

        if (!isset($json['issue'])) {
            return $response;
        }

        $method = 'on' . Container::camelize(str_replace('jira:', '', $json['webhookEvent']));
        if (!method_exists($this, $method)) {
            return new $response;
        }

        $this->{$method}($json);

        return $response;
    }

	/**
	 * @param array $data
	 * @throws \Exception
	 */
    protected function onIssueUpdated(array $data)
    {
	    $manager = $this->container->getTicketManager();
	    $em = $this->em;
	    $issues = $em->getRepository('DeskPRO:JiraIssue')->findBy(array('issue_id' => $data['issue']['id']));

	    foreach ($issues as $issue) {
		    /** @var $issue JiraIssue */
		    $state = $issue->ticket->getStateChangeRecorder();
		    $context = $manager->createSystemExecutorContext(ExecutorContext::EVENT_UPDATE, ExecutorContext::METHOD_API);
		    $context->setPersonContext($issue->ticket->agent);

		    if (isset($data['comment'])) {
			    $state->recordData('jira.comment', $data['comment']);
			    $context->getUserVars()->set('jira.comment', $data['comment']['body']);
		    }

		    if (isset($data['changelog'])) {
			    foreach ($data['changelog']['items'] as $change) {
				    if ('status' === $change['field']) {
					    $issue['status_id'] = $change['to'];
					    $em->flush($issue);
				    }
				    $state->recordData('jira.' . $change['field'], $change);
			    }
		    }

		    $manager->saveTicket($issue->ticket, $context);
	    }
    }

	/**
	 * @param array $data
	 * @throws \Exception
	 */
	protected function onIssueDeleted(array $data)
	{
		$manager = $this->container->getTicketManager();
		$em = $this->em;
		$issues = $em->getRepository('DeskPRO:JiraIssue')->findBy(array('issue_id' => $data['issue']['id']));

		foreach ($issues as $issue) {
			$ticket = $issue->ticket;
			$em->remove($issue);
			$em->flush($issue);

			$context = $manager->createSystemExecutorContext(ExecutorContext::EVENT_UPDATE, ExecutorContext::METHOD_API);
			$context->setPersonContext($ticket->agent);
			$manager->saveTicket($ticket, $context);
		}
	}
}
