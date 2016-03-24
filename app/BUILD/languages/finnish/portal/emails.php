<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

return array(
    'portal.emails.auto-close'                  => 'Tukipyyntösi "{{ticket.subject}}" suljetaan automaattisesti, koska et ole päivittänyt sitä vähään aikaan. Jos et halua että tukipyyntö sulkeutuu, lisää kommentti niin voimme edelleen auttaa sinua tässä asiassa.',
    'portal.emails.chat_transcript'             => 'Kiitos keskustelusta kanssamme. Tässä kopio keskustelustamme.',
    'portal.emails.comment_approved'            => 'Kommenttisi on julkaistu.',
    'portal.emails.comment_deleted'             => 'Kommenttiasi käytettiin sivumme parantamiseen.',
    'portal.emails.comment_thank-you'           => 'Kiitos kommentistasi {{link}}.',
    'portal.emails.comment_validate-email'      => 'Vahvistathan sähköpostiosoitteesi tästä linkistä:',
    'portal.emails.do-not-reply'                => 'Ethän vastaa tähän sähköpostiin. Viesti on lähetetty automaattisesti järjestelmästämme ja vastauksia ei lueta.',
    'portal.emails.email-too-big'               => 'Sähköpostiviestisi "{{subject}}" on liian suuri. Maksimi koko viesteille järjestelmässä on {{max_size}}. POistathan liitteet ja yrität uudelleen.',
    'portal.emails.feedback_approved'           => 'Ilmoitamme sähköpostitse kun palautteesi on luettu ja näkyvissä sivuillamme.',
    'portal.emails.feedback_closed'             => 'Lähettämäsi palaute "{{title}}" on suljettu: {{status}}',
    'portal.emails.feedback_disapproved'        => 'Lähetämmä sähköpostiviesti mikäli palautettasi ei valitettavasi julkaista sivuillamme.',
    'portal.emails.feedback_disapproved-reason' => 'Lähetämmä sähköpostiviesti mikäli palautettasi ei valitettavasi julkaista sivuillamme. {{name}} perustelee päätöstä:',
    'portal.emails.feedback_new-comment'        => '{{name}} julkaisi uuden kommentin:',
    'portal.emails.feedback_published'          => 'Lähettämäsi palaute "{{title}}" on hyväksytty ja julkaistaan sivuillamme.',
    'portal.emails.feedback_thank-you'          => 'Kiitos lähettämästäsi palautteesta "{{title}}"',
    'portal.emails.feedback_updated'            => 'Lähettämäsi palaute "{{title}}" on päivitetty ja on tallennettu uudella statuksella "{{status}}"',
    'portal.emails.feedback_validate-email'     => 'Vahvistathan sähköpostiosoitteesi tästä linkistä:',
    'portal.emails.feedback_votes'              => 'Palautteellasi on <strong>1</strong> ääntä. Katso palautteesi:|Palautteellasi on <strong>{{count}}</strong> ääntä. Katso palautteesi:',
    'portal.emails.greeting'                    => 'Hyvä {{to_name}},',
    'portal.emails.kb-explain'                  => 'Saat tämän viestin koska olet tietokantamme postituslistalla <a href="{{ deskpro_url }}">{{ deskpro_url }}</a>.<br />Haluatko jatkossa viestejämme? <a href="{{ unsubscribe_url }}">Jos haluat poistua listalta seuraa klikkaa oheista linkkiä.</a>',
    'portal.emails.kb-new-articles'             => '1 uusi artikkeli|{{count}} uutta artikkelia',
    'portal.emails.kb-updated-articles'         => '1 päivitetty artikkeli|{{count}} päivitettyä artikkelia',
    'portal.emails.label_view-online'           => 'Katso online',
    'portal.emails.message-clipped'             => '(viesti leikattu)',
    'portal.emails.password_agent-reset'        => 'Käyttäjätilisi salasana on vaihdettu. Kirjaudu uudella salasanalla',
    'portal.emails.password_reset'              => 'Vaihda salasanasi klikkamalla linkkiä:',
    'portal.emails.profile_email-new-confirm'   => 'Viimeistele vaihtoehtoisen sähköpostiosoitteen lisääminen, klikkaamalla linkkiä',
    'portal.emails.profile_email_link-validate' => 'Klikkaa linkkiä vahvistaaksesti sähköpostiosoite',
    'portal.emails.register-agent-validation'   => 'Huomioi että edustajamme on vahvistettava käyttäjätilisi. Tukipyynnöt ja muut lähettämäsi viestit ovat jonossa kunnes käyttäjätili on aktivoitu.',
    'portal.emails.register-confirm'            => 'Kiitos käyttäjätilin rekisteröinnistä.<br /><br />Vahvista sähköpostiosoittesi niin käyttäjätilisi on valmis.',
    'portal.emails.register-welcome'            => 'Kiitos rekisteröitymisestä. Jatkossa voit kirjautua sähköpostiosoitteellasi {{to_email}} tukisivuillemme:',
    'portal.emails.registration_closed'         => 'Hyvä {{name}},<br /><br /> tukipyynnöt on sallittuja vain olemassa oleville käyttäjille. Jos sinulla on jo käyttäjätili, lähetätähän viestin uudelleen rekisteröidystä sähköpostiosoitteesta.',
    'portal.emails.reject_resolved'             => 'Viestiäsi ei voitu hyväksyä, koska tukipyyntösi on jo ratkaistu. Edustajamme ei lue tai vastaa tähän viestiin.',
    'portal.emails.reject_resolved-new'         => 'Jos haluat lähettää uuden tukupyynnön lähetä sähköposti <a href="mailto:{{email_to}}">{{email_to}}</a> tai täytä online lomake:<br /><a href="{{link}}">{{link}}</a>',
    'portal.emails.reject_resolved-newemail'    => 'Jos haluat lähettää uuden tukupyynnön lähetä sähköposti <a href="mailto:{{email_to}}">{{email_to}}</a>',
    'portal.emails.ticket_access_ticket_online' => 'Tarkastele ja muokkaa tukipyyntöäsi online tilassa:',
    'portal.emails.ticket_cc-new'               => 'Sinut on lisätty tukipyyntöön, jonka lähettäjä on {{name}}.',
    'portal.emails.ticket_message_title'        => '{{date}} kello {{time}}, {{author}} kirjoitti:',
    'portal.emails.ticket_no-autoresponse'      => 'Varoitus: vahvistus sähköpostit pois käytöstä',
    'portal.emails.ticket_rate-negative'        => 'Ei',
    'portal.emails.ticket_rate-neutral'         => 'Se oli OK',
    'portal.emails.ticket_rate-positive'        => 'Kyllä',
    'portal.emails.ticket_rate-question'        => 'Oliko tämä viesti avuksi?',
    'portal.emails.ticket_received'             => 'Tukipyyntösi on vastaanotettu. Edustajamme ottaa sinuun pian yhteyttä.',
    'portal.emails.ticket_reply-confirm'        => 'Kiitos vastauksestasi. Edustajamme ottaa sinuun pian yhteyttä.',
    'portal.emails.ticket_validate'             => 'Kiitos yhteydenotosta.<br /><br />ennen kuin edustajamme lukee tai vastaa viestiisi, pitää sähköpostiosoitteesi vahvistaa.',
    'portal.emails.tickets_ommitted'            => '1 viesti huomioimatta|{{count}} viestiä huomioimatta',
    'portal.emails.view_full_history_online'    => 'Tarkastele tukipyyntöä online tilassa',
);
