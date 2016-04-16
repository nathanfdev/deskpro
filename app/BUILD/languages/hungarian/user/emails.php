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
    'user.emails.auto-close'                  => '"{{ticket.subject}}" tárgyú ügye hamarosan lezárásra kerül. Amennyiben ezt nem szeretné, mert további kérdése van az üggyel kapcsolatban, vagy újabb információra lenne szüksége, válaszoljon erre az üzenetre. Örömmel állunk rendelkezésére.',
    'user.emails.chat_transcript'             => 'Reméljük, hogy elégedett munkatársunk munkájával. Mellékeljük legutóbbi beszélgetésük kivonatát.',
    'user.emails.comment_approved'            => 'Hozzászólása publikálásra került.',
    'user.emails.comment_deleted'             => 'Hozzászólását felhasználtuk tartalmunk fejlesztéséhez.',
    'user.emails.comment_thank-you'           => 'Köszönjük hozzászólását. ({{link}})',
    'user.emails.feedback_agent-validation'   => 'Köszönjük, hogy Ön is hozzájárul munkánk sikeréhez. Üzenetének tartalmát munkatársaink ellenőrizni fogják annak érdekében, hogy kiszűrjék a nem helyénvaló bejegyzéseket oldalainkról. Az ellenőrzés rövid időn belül megtörténik.',
    'user.emails.feedback_approved'           => 'Tájékoztatjuk, hogy visszajelzése publikálásra került weboldalunkon.',
    'user.emails.feedback_closed'             => '"{{title}}" tárgyú visszajelzése lezárásra került: {{status}}',
    'user.emails.feedback_disapproved'        => 'Sajnálattal tájékoztatjuk, hogy visszajelzésének publikálása elutasításra került.',
    'user.emails.feedback_disapproved-reason' => 'Sajnálattal tájékoztatjuk, hogy visszajelzésének publikálása elutasításra került {{name}} munkatársunk által a következő okból:',
    'user.emails.feedback_new-comment'        => '{{name}} új hozzászólást küldött',
    'user.emails.feedback_published'          => '"{{title}}" tárgyú visszajelzése ellenőrzésre került, és megjelent weboldalunkon.',
    'user.emails.feedback_thank-you'          => 'Köszönjük "{{title}}" tárgyú visszajelzését',
    'user.emails.feedback_updated'            => '"{{title}}" tárgyú visszajelzése frissítésre és {{status}} státusz besorolásba került.',
    'user.emails.feedback_votes'              => 'Visszajelzésére eddig <strong>1</strong> szavazat érkezett. Tekintse meg online:|Visszajelzésére eddig <strong>{{count}}</strong> szavazat érkezett. Tekintse meg online:',
    'user.emails.greeting'                    => 'Tisztelt {{to_name}}!',
    'user.emails.label_view-online'           => 'Online megtekintés',
    'user.emails.message-clipped'             => '(Kivágott üzenet)',
    'user.emails.password_agent-reset'        => 'Munkatársunk alaphelyzetbe állította az Ön jelszavát. Ezután a következő adatokkal tud bejelentkezni weboldalunkon:',
    'user.emails.password_reset'              => 'Ön jelszócserét kért weboldalunkon. Amennyiben meg kívánja erősíteni szándékát, a jelszócseréhez kattintson a következő linkre:',
    'user.emails.profile_email-new-confirm'   => 'Másodlagos e-mail címének megerősítéséhez kérjük kattintson a következő linkre:',
    'user.emails.profile_email_link-validate' => 'E-mail címének megerősítéséhez kérjük kattintson a következő linkre:',
    'user.emails.register-confirm'            => 'Köszönjük regisztrációját.<br /><br />Mielőtt használatba vehetné ügyfélfiókját, kattintson a következő linkre e-mail címének megerősítéséhez:',
    'user.emails.registration_closed'         => 'Tisztelt {{name}}!<br /><br /><br /><br />Ügyfélszolgálati rendszerünk csak regisztrált felhasználóktól fogad el üzenetet. Amennyiben Ön regisztrált felhasználónk, kérjük használja rendszerünkben nyilvántartott e-mail címét az üzenetküldéshez.',
    'user.emails.ticket_access_ticket_online' => 'Online megtekintés és szerkesztés:',
    'user.emails.ticket_cc-new'               => 'Ön hozzárendelésre került egy {{name}} által indított ügyhöz.',
    'user.emails.ticket_flood'                => 'Túl sok egymásután érkező üzenet érkezett Öntől rendszerünkbe, amelyeket valószínűleg egy automata küldött.<br /><br />A levélküldési hurok megállítása érdekében nem küldünk több automatikus üzenetet Önnek.',
    'user.emails.ticket_message_title'        => '{{author}} - {{date}}, {{time}}',
    'user.emails.ticket_no-autoresponse'      => 'Figyelem: Megerősítő e-mail kikapcsolva',
    'user.emails.ticket_rate-negative'        => 'Nem',
    'user.emails.ticket_rate-neutral'         => 'Rendben volt',
    'user.emails.ticket_rate-positive'        => 'Igen',
    'user.emails.ticket_rate-question'        => 'Hasznos volt Önnek ez az üzenet?',
    'user.emails.ticket_received'             => 'Megkaptuk üzenetét. Munkatársunk hamarosan felveszi Önnel a kapcsolatot.',
    'user.emails.ticket_reply-confirm'        => 'Válaszüzenetét megkaptuk. Munkatársunk hamarosan válaszol Önnek.',
    'user.emails.ticket_validate'             => 'Mielőtt munkatársaink feldolgozhatnák üzenetét,<br /><br />meg kell erősítenie e-mail címét.',
);
