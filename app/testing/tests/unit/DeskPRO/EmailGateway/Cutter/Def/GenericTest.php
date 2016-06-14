<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DpUnitTests\DeskPRO\EmailGateway\Cutter\Def;

/**
 * Unit tests suite for email cutting.
 *
 * NOTE: The test strings are quite long and therefore are declared at the bottom of the class.
 */
class GenericTest extends \DpUnitTestCase
{
    /**
     * @var \Application\DeskPRO\EmailGateway\Cutter\Def\Generic;
     */
    private $cutter;

    public function runBefore()
    {
        $this->cutter = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
    }

    /**
     * Tests the main cutting feature of the class.
     */
    public function testTextCutting()
    {
        $cut_text = $this->cutter->cutQuoteBlock($this->replyTextEmail, false);
        $this->assertEquals($this->expectedCutReplyTextEmail, $cut_text);
    }

    public function testHtmlCutting()
    {
        $cut_text = $this->cutter->cutQuoteBlock($this->replyHtmlEmail, true);
        $this->assertEquals($this->expectedCutReplyHtmlEmail, $cut_text);
    }

    private $fullReplyEmail = <<<EOT
Delivered-To: deskpro@example.com
Received: by 10.140.81.70 with SMTP id e64csp1522592qgd;
Sun, 21 Jun 2015 07:52:23 -0700 (PDT)
X-Received: by 10.202.188.139 with SMTP id m133mr20265663oif.73.1434898343581;
Sun, 21 Jun 2015 07:52:23 -0700 (PDT)
Return-Path: <client@example.com>
Received: from mail-oi0-f41.google.com (mail-oi0-f41.google.com. [209.85.218.41])
by mx.google.com with ESMTPS id m13si10222136obe.27.2015.06.21.07.52.22
for <deskpro@example.com>
(version=TLSv1.2 cipher=ECDHE-RSA-AES128-GCM-SHA256 bits=128/128);
Sun, 21 Jun 2015 07:52:23 -0700 (PDT)
Received-SPF: pass (google.com: domain of client@example.com designates 209.85.218.41 as permitted sender) client-ip=209.85.218.41;
Authentication-Results: mx.google.com;
spf=pass (google.com: domain of client@example.com designates 209.85.218.41 as permitted sender) smtp.mail=client@example.com
Received: by mail-oi0-f41.google.com with SMTP id y130so86854487oiy.0
for <deskpro@example.com>; Sun, 21 Jun 2015 07:52:22 -0700 (PDT)
X-Google-DKIM-Signature: v=1; a=rsa-sha256; c=relaxed/relaxed;
d=1e100.net; s=20130820;
h=x-gm-message-state:mime-version:in-reply-to:references:date
:message-id:subject:from:to:content-type;
bh=OMkTXV6z1OLI4esNirRTVgk0kl+giqTT4EIkCe7S6kg=;
b=ESVN0jES6tFVK+BZVIX3L77qT0M40uHweNJrCjRlea98Is0IC7udcKQ+gwoGgmlIO/
qNz0bIvQaCUjWCHRqJicQIXPPv2eLnpLwxwXK650Y99VlxK6Sr8DgrJ6o37zNmrZlmqz
qS7hwi0hz97d3n/z37lIS9ZSGm6gw6CH9Qtc1pvMPCqRlS8aPO0uMSXGept5EU7+E8nt
R+8NjEsEVAj1n38MEnTGMGePsB7ludtfyzihdShJ03UT6f7wORi8kyw94WbPTEItZaI/
VklwoMO3bF+Tw7ak2J3ttLGfMwqR3tchWzxdcaVo37CZyOP2rSpK5X763iIjuPzEjfOr
bQ/g==
X-Gm-Message-State: ALoCoQnLs61QqeIBFVC8jU0g+l8lJ4lp+q65L5wbpyVUEZcX3JnprjNR8e7lbwAqil7oi+tgpcmD
MIME-Version: 1.0
X-Received: by 10.202.223.9 with SMTP id w9mr20340090oig.14.1434898342737;
Sun, 21 Jun 2015 07:52:22 -0700 (PDT)
Received: by 10.76.27.99 with HTTP; Sun, 21 Jun 2015 07:52:22 -0700 (PDT)
X-Originating-IP: [86.135.68.215]
In-Reply-To: <1434893740-JN7UNRME83TUZ6PHO0VPYJGDKYI6C82PLZU03VVV@deskpro-message>
References: <TICKET-FYDPB82PQMM5KQ5J7.1@d41d8cd98f00b204e9800998ecf8427e>
<1434893740-JN7UNRME83TUZ6PHO0VPYJGDKYI6C82PLZU03VVV@deskpro-message>
Date: Sun, 21 Jun 2015 15:52:22 +0100
Message-ID: <CAEpr+AUiY0FBZ7CgBdSbR+y1e5PtUDHsVd2U07ia7Z-bJV_tNQ@mail.gmail.com>
Subject: Re: [#154 NEW TICKET] Test Message #1335 -- 2015-06-21 -- 38
From: Test Client <client@example.com>
To: Test Agent <deskpro@example.com>
Content-Type: multipart/alternative; boundary=001a113d38540212700519084eb2

--001a113d38540212700519084eb2
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

Another reply!

On Sun, Jun 21, 2015 at 2:35 PM, Test Client <client@example.com>
wrote:

>   =3D=3D=3D  REPLY ABOVE [Help <http://deskpro.com/l/reply>] =3D=3D=3D
> --- Your reply will be sent to the user Test Agent <
> agent@example.com>
>
>   Test Message #1335 -- 2015-06-21 -- 38
>
>
> Ticket Test Agent <agent@example.com> created a new ticket
>
>  View this ticket online:
> http://localhost:8000/index.php/agent/#app.tickets,t:154  ID 154  Ticket
> Starter Test Agent <agent@example.com>  Status Awaiting Agent
> Agent Unassigned  Department Support
>
> http://localhost:8000/ =C2=B7 Manage Your Notification Settings
> <http://localhost:8000/index.php/agent/#settings.ticket-notify>
>   (#NPDACW7YYHQZ8P6ST)
>
>

--001a113d38540212700519084eb2
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

<div dir=3D"ltr">Another reply!</div><div class=3D"gmail_extra"><br><div cl=
ass=3D"gmail_quote">On Sun, Jun 21, 2015 at 2:35 PM, Guillaume Pasquet <spa=
n dir=3D"ltr">&lt;<a href=3D"mailto:deskpro@example.com" target=3D"_blank">=
deskpro@example.com</a>&gt;</span> wrote:<br><blockquote class=3D"gmail_quo=
te" style=3D"margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex"=
><u></u>


<div style=3D"font-family:Calibri,Helvetica,Arial,sans-serif;line-height:12=
5%;color:#242424;font-size:14px">

<div style=3D"font-family:Calibri,Helvetica,Arial,sans-serif;line-height:12=
5%;color:#242424;font-size:14px">
=3D=3D=3D <a name=3D"14e165500c834223_DP_TOP_MARK" style=3D"color:#000;text=
-decoration:none;font-size:1px;line-height:1px;min-height:1px;overflow:hidd=
en;margin:0;padding:0;display:inline">=C2=A0</a>REPLY=20
ABOVE [<a href=3D"http://deskpro.com/l/reply" style=3D"color:#0065a3;text-d=
ecoration:underline" target=3D"_blank">Help</a>] =3D=3D=3D<br>
--- Your reply will be sent to the user Guillaume Pasquet=20
&lt;<a href=3D"mailto:g@bitimplosion.com" target=3D"_blank">agent@example.=
com</a>&gt;<br><br><table width=3D"100%" border=3D"0" cellpadding=3D"0" cel=
lspacing=3D"0"><tbody><tr><td style=3D"padding:0;font-family:Calibri,Helvet=
ica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px">

<table border=3D"0" width=3D"100%" cellpadding=3D"0" cellspacing=3D"0">
<tbody><tr>
<td valign=3D"top" style=3D"padding:0;font-family:Calibri,Helvetica,Arial,s=
ans-serif;line-height:125%;color:#242424;font-size:14px">				<div dir=3D"lt=
r" lang=3D"en" name=3D"dp_message_199_begin">Test Message #1335 -- 2015-06-=
21 -- 38</div>
<a name=3D"14e165500c834223_dp_message_199_end" style=3D"color:#000;text-de=
coration:none;font-size:1px;line-height:1px;min-height:1px;overflow:hidden;=
margin:0;padding:0;display:inline"></a>
=09

<br><br><h1 style=3D"font-family:&#39;Helvetica Neue&#39;,Helvetica,Arial,s=
ans-serif;font-weight:bold;margin-left:0;margin-right:0;margin-top:7px;marg=
in-bottom:7px;font-size:36px">Ticket</h1>

Test Agent &lt;<a href=3D"mailto:agent@example.com" target=3D"_blan=
k">agent@example.com</a>&gt; created a new ticket

<br><br><table border=3D"0" cellspacing=3D"0" cellpadding=3D"0" style=3D"bo=
rder-left:1px solid #bababa;border-right:1px solid #bababa;border-bottom:1p=
x solid #bababa">
<tbody><tr>
<td colspan=3D"2" style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,=
sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px sol=
id #bababa">
View this ticket online: <a href=3D"http://localhost:8000/index.php/agen=
t/#app.tickets,t:154" title=3D"(#NPDACW7YYHQZ8P6ST)" style=3D"color:#0065a3=
;text-decoration:underline" target=3D"_blank">http://localhost:8000/index.p=
hp/agent/#app.tickets,t:154</a>
</td>
</tr>
<tr>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;lin=
e-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">ID=
</td>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;l=
ine-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">=
154</td>
</tr>
<tr>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;lin=
e-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">Ti=
cket Starter</td>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;l=
ine-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">
Test Agent mailto:agent@example.com" target=3D"_b=
lank">agent@example.com</a>&gt;
</td>
</tr>
<tr>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;lin=
e-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">St=
atus</td>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;l=
ine-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">
Awaiting Agent
</td>
</tr>
<tr>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;lin=
e-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">Ag=
ent</td>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;l=
ine-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">=
Unassigned</td>
</tr>
<tr>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;lin=
e-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">De=
partment</td>
<td style=3D"padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif=
;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa=
">Support</td>
</tr>
</tbody></table>
</td>
</tr>
<tr>
<td valign=3D"top" style=3D"padding:0;font-family:Calibri,Helvetica,Arial,s=
ans-serif;line-height:125%;color:#ababab;font-size:14px">
<div style=3D"font-family:Calibri,Helvetica,Arial,sans-serif;line-height=
:125%;color:#ababab;font-size:14px">
<div style=3D"font-family:Calibri,Helvetica,Arial,sans-serif;line-heigh=
t:100%;color:#ababab;font-size:12px;padding:0;margin-top:15px;border-top:2p=
x solid #c5c5c5">=C2=A0</div>
<a href=3D"http://localhost:8000/" style=3D"color:#ababab;text-decoration:u=
nderline" target=3D"_blank">http://localhost:8000/</a>=09
=C2=B7 <a href=3D"http://localhost:8000/index.php/agent/#settings.ticket-no=
tify" style=3D"color:#ababab;text-decoration:underline" target=3D"_blank">M=
anage Your Notification=20
Settings</a>
</div>
</td>
</tr>
</tbody></table>
<span style=3D"font-family:Calibri,Helvetica,Arial,sans-serif;line-height:1=
25%;color:#e8e8e8;font-size:1px">(#NPDACW7YYHQZ8P6ST)</span>


</td></tr></tbody></table>
</div>

<a name=3D"14e165500c834223_DP_BOTTOM_MARK" style=3D"color:#000;text-decor=
ation:none;font-size:1px;line-height:1px;min-height:1px;overflow:hidden;mar=
gin:0;padding:0;display:inline">=C2=A0</a>
</div>

</blockquote></div><br></div>

--001a113d38540212700519084eb2--
EOT;

    /**
     * @var The text only part of a reply email.
     */
    private $replyTextEmail = <<<EOT
Another reply!

On Sun, Jun 21, 2015 at 2:35 PM, Test Client <client@example.com>
wrote:

>   =3D=3D=3D  REPLY ABOVE [Help <http://deskpro.com/l/reply>] =3D=3D=3D
> --- Your reply will be sent to the user Test Agent <
> agent@example.com>
>
>   Test Message #1335 -- 2015-06-21 -- 38
>
>
> Ticket Test Agent <agent@example.com> created a new ticket
>
>  View this ticket online:
> http://localhost:8000/index.php/agent/#app.tickets,t:154  ID 154  Ticket
> Starter Test Agent <agent@example.com>  Status Awaiting Agent
> Agent Unassigned  Department Support
>
> http://localhost:8000/ =C2=B7 Manage Your Notification Settings
> <http://localhost:8000/index.php/agent/#settings.ticket-notify>
>   (#NPDACW7YYHQZ8P6ST)
>
>
EOT;

    private $expectedCutReplyTextEmail = <<<EOT
Another reply!

On Sun, Jun 21, 2015 at 2:35 PM, Test Client <client@example.com>
wrote:


EOT;

    /**
     * @var The completed and sanitized HTML part of a reply email.
     */
    private $replyHtmlEmail = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head></head><body>
<div dir="ltr">Another reply!</div><div class="gmail_extra"><br><div class="gmail_quote">On Sun, Jun 21, 2015 at 2:35 PM, Guillaume Pasquet <span dir="ltr">&lt;<a href="mailto:deskpro@example.com" target="_blank">deskpro@example.com</a>&gt;</span> wrote:<br><blockquote class="gmail_quote" style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex"><u></u>


<div style="font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px">

<div style="font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px">
=== <a name="14e165500c834223_DP_TOP_MARK" style="color:#000;text-decoration:none;font-size:1px;line-height:1px;min-height:1px;overflow:hidden;margin:0;padding:0;display:inline">=C2=A0</a>REPLY=20
ABOVE [<a href="http://deskpro.com/l/reply" style="color:#0065a3;text-decoration:underline" target="_blank">Help</a>] ===<br>
--- Your reply will be sent to the user Guillaume Pasquet=20
&lt;<a href="mailto:g@bitimplosion.com" target="_blank">agent@example.com</a>&gt;<br><br><table width="100%" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td style="padding:0;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px">

<table border="0" width="100%" cellpadding="0" cellspacing="0">
<tbody><tr>
<td valign="top" style="padding:0;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px">				<div dir="ltr" lang="en" name="dp_message_199_begin">Test Message #1335 -- 2015-06-21 -- 38</div>
<a name="14e165500c834223_dp_message_199_end" style="color:#000;text-decoration:none;font-size:1px;line-height:1px;min-height:1px;overflow:hidden;margin:0;padding:0;display:inline"></a>
=09

<br><br><h1 style="font-family:&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif;font-weight:bold;margin-left:0;margin-right:0;margin-top:7px;margin-bottom:7px;font-size:36px">Ticket</h1>

Test Agent &lt;<a href="mailto:agent@example.com" target="_blank">agent@example.com</a>&gt; created a new ticket

<br><br><table border="0" cellspacing="0" cellpadding="0" style="border-left:1px solid #bababa;border-right:1px solid #bababa;border-bottom:1px solid #bababa">
<tbody><tr>
<td colspan="2" style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">
View this ticket online: <a href="http://localhost:8000/index.php/agent/#app.tickets,t:154" title="(#NPDACW7YYHQZ8P6ST)" style="color:#0065a3;text-decoration:underline" target="_blank">http://localhost:8000/index.php/agent/#app.tickets,t:154</a>
</td>
</tr>
<tr>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">ID</td>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">154</td>
</tr>
<tr>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">Ticket Starter</td>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">
Test Agent mailto:agent@example.com" target="_blank">agent@example.com</a>&gt;
</td>
</tr>
<tr>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">Status</td>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">
Awaiting Agent
</td>
</tr>
<tr>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">Agent</td>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">Unassigned</td>
</tr>
<tr>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">Department</td>
<td style="padding:6px;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px;border-top:1px solid #bababa">Support</td>
</tr>
</tbody></table>
</td>
</tr>
<tr>
<td valign="top" style="padding:0;font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#ababab;font-size:14px">
<div style="font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#ababab;font-size:14px">
<div style="font-family:Calibri,Helvetica,Arial,sans-serif;line-height:100%;color:#ababab;font-size:12px;padding:0;margin-top:15px;border-top:2px solid #c5c5c5">=C2=A0</div>
<a href="http://localhost:8000/" style="color:#ababab;text-decoration:underline" target="_blank">http://localhost:8000/</a>=09
=C2=B7 <a href="http://localhost:8000/index.php/agent/#settings.ticket-notify" style="color:#ababab;text-decoration:underline" target="_blank">Manage Your Notification=20
Settings</a>
</div>
</td>
</tr>
</tbody></table>
<span style="font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#e8e8e8;font-size:1px">(#NPDACW7YYHQZ8P6ST)</span>


</td></tr></tbody></table>
</div>

<a name="14e165500c834223_DP_BOTTOM_MARK" style="color:#000;text-decoration:none;font-size:1px;line-height:1px;min-height:1px;overflow:hidden;margin:0;padding:0;display:inline">=C2=A0</a>
</div>

</blockquote></div><br></div>
</body></html>
EOT;

    private $expectedCutReplyHtmlEmail = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head></head><body>
<div dir="ltr">Another reply!</div><div class="gmail_extra"><br><div class="gmail_quote">On Sun, Jun 21, 2015 at 2:35 PM, Guillaume Pasquet <span dir="ltr">&lt;<a href="mailto:deskpro@example.com" target="_blank">deskpro@example.com</a>&gt;</span> wrote:<br><blockquote class="gmail_quote" style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex"><u></u>


<div style="font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px">

<div style="font-family:Calibri,Helvetica,Arial,sans-serif;line-height:125%;color:#242424;font-size:14px">
EOT;
}
