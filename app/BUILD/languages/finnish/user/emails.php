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
    'user.emails.auto-close'                  => 'Tukipyyntösi "{{ticket.subject}}" suljetaan automaattisesti, koska et ole päivittänyt sitä vähään aikaan. Jos et halua että tukipyyntö sulkeutuu, lisää kommentti niin voimme edelleen auttaa sinua tässä asiassa.',
    'user.emails.chat_transcript'             => 'Kiitos keskustelusta kanssamme. Tässä kopio keskustelustamme.',
    'user.emails.comment_approved'            => 'Kommenttisi on julkaistu.',
    'user.emails.comment_deleted'             => 'Kommenttiasi käytettiin sivumme parantamiseen.',
    'user.emails.comment_thank-you'           => 'Kiitos kommentistasi {{link}}.',
    'user.emails.comment_validate-email'      => 'Vahvistathan sähköpostiosoitteesi tästä linkistä:',
    'user.emails.do-not-reply'                => 'Ethän vastaa tähän sähköpostiin. Viesti on lähetetty automaattisesti järjestelmästämme ja vastauksia ei lueta.',
    'user.emails.email-too-big'               => 'Sähköpostiviestisi "{{subject}}" on liian suuri. Maksimi koko viesteille järjestelmässä on {{max_size}}. POistathan liitteet ja yrität uudelleen.',
    'user.emails.feedback_agent-validation'   => 'Huomioithan että palautteesi näkyy julkisena vasta kun se on esiluettu.',
    'user.emails.feedback_approved'           => 'Ilmoitamme sähköpostitse kun palautteesi on luettu ja näkyvissä sivuillamme.',
    'user.emails.feedback_closed'             => 'Lähettämäsi palaute "{{title}}" on suljettu: {{status}}',
    'user.emails.feedback_disapproved'        => 'Lähetämmä sähköpostiviesti mikäli palautettasi ei valitettavasi julkaista sivuillamme.',
    'user.emails.feedback_disapproved-reason' => 'Lähetämmä sähköpostiviesti mikäli palautettasi ei valitettavasi julkaista sivuillamme. {{name}} perustelee päätöstä:',
    'user.emails.feedback_new-comment'        => '{{name}} julkaisi uuden kommentin:',
    'user.emails.feedback_published'          => 'Lähettämäsi palaute "{{title}}" on hyväksytty ja julkaistaan sivuillamme.',
    'user.emails.feedback_thank-you'          => 'Kiitos lähettämästäsi palautteesta "{{title}}"',
    'user.emails.feedback_updated'            => 'Lähettämäsi palaute "{{title}}" on päivitetty ja on tallennettu uudella statuksella "{{status}}"',
    'user.emails.feedback_validate-email'     => 'Aktivoi sähköpostiosoitteesi oheisesta linkistä:',
    'user.emails.feedback_votes'              => 'Palautteellasi on <strong>1</strong> ääntä. Katso palautteesi:|Palautteellasi on <strong>{{count}}</strong> ääntä. Katso palautteesi:',
    'user.emails.greeting'                    => 'Hyvä {{to_name}},',
    'user.emails.kb-explain'                  => 'Saat tämän viestin koska olet tietokantamme postituslistalla <a href="{{ deskpro_url }}">{{ deskpro_url }}</a>.<br />Haluatko jatkossa viestejämme? <a href="{{ unsubscribe_url }}">Jos haluat poistua listalta seuraa klikkaa oheista linkkiä.</a>',
    'user.emails.kb-new-articles'             => '1 uusi artikkeli|{{count}} uutta artikkelia',
    'user.emails.kb-updated-articles'         => '1 päivitetty artikkeli|{{count}} päivitettyä artikkelia',
    'user.emails.label_view-online'           => 'Katso online',
    'user.emails.message-clipped'             => '(viesti leikattu)',
    'user.emails.password_agent-reset'        => 'Käyttäjätilisi salasana on vaihdettu. Kirjaudu uudella salasanalla',
    'user.emails.password_reset'              => 'Vaihda salasanasi klikkamalla linkkiä:',
    'user.emails.profile_email-new-confirm'   => 'Viimeistele vaihtoehtoisen sähköpostiosoitteen lisääminen, klikkaamalla linkkiä',
    'user.emails.profile_email_link-validate' => 'Klikkaa linkkiä vahvistaaksesti sähköpostiosoite',
    'user.emails.register-agent-validation'   => 'Huomioi että edustajamme on vahvistettava käyttäjätilisi. Tukipyynnöt ja muut lähettämäsi viestit ovat jonossa kunnes käyttäjätili on aktivoitu.',
    'user.emails.register-confirm'            => 'Kiitos käyttäjätilin rekisteröinnistä.<br /><br />Vahvista sähköpostiosoittesi niin käyttäjätilisi on valmis.',
    'user.emails.register-welcome'            => 'Kiitos rekisteröitymisestä. Jatkossa voit kirjautua sähköpostiosoitteellasi {{to_email}} tukisivuillemme:',
    'user.emails.registration_closed'         => 'Hyvä {{name}},<br /><br /> tukipyynnöt on sallittuja vain olemassa oleville käyttäjille. Jos sinulla on jo käyttäjätili, lähetätähän viestin uudelleen rekisteröidystä sähköpostiosoitteesta.',
    'user.emails.reject_resolved'             => 'Viestiäsi ei voitu hyväksyä, koska tukipyyntösi on jo ratkaistu. Edustajamme ei lue tai vastaa tähän viestiin.',
    'user.emails.reject_resolved-new'         => 'Jos haluat lähettää uuden tukupyynnön lähetä sähköposti <a href="mailto:{{email_to}}">{{email_to}}</a> tai täytä online lomake:<br /><a href="{{link}}">{{link}}</a>',
    'user.emails.reject_resolved-newemail'    => 'Jos haluat lähettää uuden tukupyynnön lähetä sähköposti <a href="mailto:{{email_to}}">{{email_to}}</a>',
    'user.emails.ticket_access_ticket_online' => 'Tarkastele ja muokkaa tukipyyntöäsi online tilassa:',
    'user.emails.ticket_cc-new'               => 'Sinut on lisätty tukipyyntöön, jonka lähettäjä on {{name}}.',
    'user.emails.ticket_message_title'        => '{{date}} kello {{time}}, {{author}} kirjoitti:',
    'user.emails.ticket_no-autoresponse'      => 'Varoitus: vahvistus sähköpostit pois käytöstä',
    'user.emails.ticket_rate-negative'        => 'Ei',
    'user.emails.ticket_rate-neutral'         => 'Se oli OK',
    'user.emails.ticket_rate-positive'        => 'Kyllä',
    'user.emails.ticket_rate-question'        => 'Oliko tämä viesti avuksi?',
    'user.emails.ticket_received'             => 'Tukipyyntösi on vastaanotettu. Edustajamme ottaa sinuun pian yhteyttä.',
    'user.emails.ticket_reply-confirm'        => 'Kiitos vastauksestasi. Edustajamme ottaa sinuun pian yhteyttä.',
    'user.emails.ticket_validate'             => 'Kiitos yhteydenotosta.<br /><br />ennen kuin edustajamme lukee tai vastaa viestiisi, pitää sähköpostiosoitteesi vahvistaa.',
    'user.emails.tickets_ommitted'            => '1 viesti huomioimatta|{{count}} viestiä huomioimatta',
    'user.emails.view_full_history_online'    => 'Tarkastele tukipyyntöä online tilassa',
);
