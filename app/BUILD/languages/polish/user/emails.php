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
    'portal.emails.auto-close'                  => 'Twoja petycja "{{ticket.subject}}" będzie zamknięta automatycznie, bo nie zaktualizowałeś jej później. Jeżeli nie chcesz, żeby twoja petycja została zamknięta, możesz dodać nową odpowiedź, wtedy nasi pracownicy pomogą ci później.',
    'portal.emails.chat_transcript'             => 'Dziękujemy za czatowanie z nami. Oto log Twojego czatu:',
    'portal.emails.comment_approved'            => 'Twój komentarz został opublikowany.',
    'portal.emails.comment_deleted'             => 'Twój komentarz został użyty, żeby udoskonalić stronę.',
    'portal.emails.comment_thank-you'           => 'Dziękujemy za Twój komentarz dotyczący {{link}}.',
    'portal.emails.comment_validate-email'      => 'Potwierdź swój adres email online, klikając na następujący link:',
    'portal.emails.do-not-reply'                => 'Nie odpowiadaj na tę wiadomość. Ta wiadomość została zgenerowana automatycznie i odpowiedź nie zostanie przeczytana przez naszych pracowników.',
    'portal.emails.email-too-big'               => 'Twoja wiadomość "{{subject}}" jest zbyt duża. Maksymalna wielkość wynosi {{max_size}}. Usuń jakikolwiek załącznik i spróbuj ponownie.',
    'portal.emails.feedback_approved'           => 'Chcemy Cię powiadomić, że Twoja opinia została zatwierdzona i teraz jest widoczna na naszej stronie.',
    'portal.emails.feedback_closed'             => 'Wysłana przez Ciebie opinia "{{title}}" została zamknięta: {{status}}',
    'portal.emails.feedback_disapproved'        => 'Chcemy Cię powiadomić, że niestety Twoja opinia została odrzucona i nie pojawi się publicznie.',
    'portal.emails.feedback_disapproved-reason' => 'Chcemy Cię powiadomić, że niestety Twoja opinia została odrzucona i nie pojawi się publicznie. {{name}} podał(a) następującą przyczynę:',
    'portal.emails.feedback_new-comment'        => '{{name}} opublikował(a) nowy komentarz:',
    'portal.emails.feedback_published'          => 'Wysłana przez Ciebie opinia "{{title}}" została potwierdzona i teraz pojawi sie na naszej stronie.',
    'portal.emails.feedback_thank-you'          => 'Dziękujemy za wysłaną przez Ciebie opinię "{{title}}"',
    'portal.emails.feedback_updated'            => 'Wysłana przez Ciebie opinia "{{title}}" została zaktualizowana. W tej chwili posiada status "{{status}}"',
    'portal.emails.feedback_validate-email'     => 'Potwierdź swój adres email online, klikając na następujący link:',
    'portal.emails.feedback_votes'              => 'Twoja opinia obecnie ma <strong>1</strong> głos. Zobacz Twoją opinię online:|Twoja opinia obecnie ma głosów - <strong>{{count}}</strong>. Zobacz Twoją opinię online:',
    'portal.emails.greeting'                    => 'Szanowny(a) {{to_name}},',
    'portal.emails.kb-explain'                  => 'Otrzymałeś tą wiadomość, ponieważ subskrybujesz naszą bazę wiedzy na <a href="{{ deskpro_url }}">{{ deskpro_url }}</a>.<br />Jeśli nie chcesz otrzymywać tych maili, <a href="{{ unsubscribe_url }}">Kliknij tutaj. </a>',
    'portal.emails.kb-new-articles'             => '1 Nowy artykuł | {{count}} Nowych artykułów',
    'portal.emails.kb-updated-articles'         => '1 Zaktualizowany artykuł | {{count}} zaktualizowanych artykułów',
    'portal.emails.label_view-online'           => 'Zobacz online',
    'portal.emails.message-clipped'             => '(Reszta wiadomości zostala ukryta)',
    'portal.emails.password_agent-reset'        => 'Nasz pracownik zmienił Twoje hasło. Możesz się zalogować z tymi poświadczeniami.',
    'portal.emails.password_reset'              => 'Poprosiłeś(aś) o zmianę hasła. Żeby zmienić hasło, kliknij na link poniżej:',
    'portal.emails.profile_email-new-confirm'   => 'Żeby zakończyć dodanie wtórnego adresu email, kliknij na następujący link potwierdzający',
    'portal.emails.profile_email_link-validate' => 'Kliknij na następujący link, żeby potwierdzić swój adres email',
    'portal.emails.register-agent-validation'   => 'Uwaga: Zanim twoje konto będzie w pełni aktywne, nasz agent musi ręcznie je sprawdzić. Zgłoszenia oraz inna zawartość, którą przesyłasz, zostanie zatrzymana w kolejce oczekujących na sprawdzenie do czasu sprawdzenia twojego konta.',
    'portal.emails.register-confirm'            => 'Dziękujemy za rejestrację konta.<br /><br />Żeby zacząć używać swojego konta, potwierdź swój adres email klikając na następujący link',
    'portal.emails.register-welcome'            => 'Dziękujemy za rejestrację. Możesz już się logować za pomocą adresu email {{to_email}} w systemie zgłoszeń.',
    'portal.emails.registration_closed'         => 'Szanowny(a) {{name}},<br /><br /><br /><br />Nowe petycje mogą być wysłane tylko przez zarejestrowanych użytkowników Helpdesku. Jeżeli już masz konto, napisz do nas ze swojego zarejestrowanego adresu email.',
    'portal.emails.reject_resolved'             => 'Twoja wiadomość nie została zaakceptowana ponieważ twoje zgłoszenie jest oznaczone jako rozwiązane. Konsultant nie przeczyta ani nie odpisze na tą wiadomość.',
    'portal.emails.reject_resolved-new'         => 'Jeśli chcesz utworzyć nowe zgłoszenie, możesz wysłać nowy email do <a href="mailto:{{email_to}}">{{email_to}}</a> lub możesz wysłać przez formularz online dostępny na:<br /><a href="{{link}}">{{link}}</a>',
    'portal.emails.reject_resolved-newemail'    => 'Jeśli chcesz utworzyć nowe zgłoszenie, możesz wysłać nowy email do <a href="mailto:{{email_to}}">{{email_to}}</a>',
    'portal.emails.ticket_access_ticket_online' => 'Zobacz i kieruj zgłoszeniem online:',
    'portal.emails.ticket_cc-new'               => 'Zostałeś dołączony do zgłoszenia, stworzonego przez {{name}}.',
    'portal.emails.ticket_flood'                => 'Niedawno wysłałeś(aś) email do naszego Helpdesku. Nasz system automatyczny wykrył, że wysłałeś(aś) wiele wiadomości w krótkim czasie, jak by były automatycznie zgenerowane.<br /><br />Żeby się zabezpieczyć przed pętlą automatycznych odpowiedzi, nasz Helpdesk nie będzie więcej wysyłał do Ciebie automatycznych powiadomień.',
    'portal.emails.ticket_message_title'        => '{{date}} o {{time}}, {{author}} napisał(a):',
    'portal.emails.ticket_no-autoresponse'      => 'Ostrzeżenie: emaile potwierdzające zostały wyłączone',
    'portal.emails.ticket_rate-negative'        => 'Nie',
    'portal.emails.ticket_rate-neutral'         => 'Co nieco',
    'portal.emails.ticket_rate-positive'        => 'Tak',
    'portal.emails.ticket_rate-question'        => 'Czy ta wiadomość była przydatna?',
    'portal.emails.ticket_received'             => 'Twoja petycja została otrzymana. Jeden z naszych pracowników wkrótce na nią odpowie.',
    'portal.emails.ticket_reply-confirm'        => 'Dziękujemy za Twoją odpowiedź. Jeden z naszych pracowników wkrótce na nią odpowie.',
    'portal.emails.ticket_validate'             => 'Dziękujemy za Twoją wiadomość.<br /><br />Zanim nasi pracownicy przeczytają i odpowiedzą na nią, musisz potwierdzić swój adres email.',
    'portal.emails.tickets_ommitted'            => '1 wiadomość została pominięta| Pominiętych wiadomości - {{count}}',
    'portal.emails.view_full_history_online'    => 'Zobacz całą treść petycji online',
    'user.emails.auto-close'                    => 'Twoja petycja "{{ticket.subject}}" będzie zamknięta automatycznie, bo nie zaktualizowałeś jej później. Jeżeli nie chcesz, żeby twoja petycja została zamknięta, możesz dodać nową odpowiedź, wtedy nasi pracownicy pomogą ci później.',
    'user.emails.chat_transcript'               => 'Dziękujemy za czatowanie z nami. Oto log Twojego czatu:',
    'user.emails.comment_approved'              => 'Twój komentarz został opublikowany.',
    'user.emails.comment_deleted'               => 'Twój komentarz został użyty, żeby udoskonalić stronę.',
    'user.emails.comment_thank-you'             => 'Dziękujemy za Twój komentarz dotyczący {{link}}.',
    'user.emails.comment_validate-email'        => 'Potwierdź swój adres email online, klikając na następujący link:',
    'user.emails.do-not-reply'                  => 'Nie odpowiadaj na tę wiadomość. Ta wiadomość została zgenerowana automatycznie i odpowiedź nie zostanie przeczytana przez naszych pracowników.',
    'user.emails.email-too-big'                 => 'Twoja wiadomość "{{subject}}" jest zbyt duża. Maksymalna wielkość wynosi {{max_size}}. Usuń jakikolwiek załącznik i spróbuj ponownie.',
    'user.emails.feedback_agent-validation'     => 'Uwaga! Twoja opinia nie pojawi się publicznie na naszej stronie, dopóki jeden z naszych pracowników jej nie przejrzy.',
    'user.emails.feedback_approved'             => 'Chcemy Cię powiadomić, że Twoja opinia została zatwierdzona i teraz jest widoczna na naszej stronie.',
    'user.emails.feedback_closed'               => 'Wysłana przez Ciebie opinia "{{title}}" została zamknięta: {{status}}',
    'user.emails.feedback_disapproved'          => 'Chcemy Cię powiadomić, że niestety Twoja opinia została odrzucona i nie pojawi się publicznie.',
    'user.emails.feedback_disapproved-reason'   => 'Chcemy Cię powiadomić, że niestety Twoja opinia została odrzucona i nie pojawi się publicznie. {{name}} podał(a) następującą przyczynę:',
    'user.emails.feedback_new-comment'          => '{{name}} opublikował(a) nowy komentarz:',
    'user.emails.feedback_published'            => 'Wysłana przez Ciebie opinia "{{title}}" została potwierdzona i teraz pojawi sie na naszej stronie.',
    'user.emails.feedback_thank-you'            => 'Dziękujemy za wysłaną przez Ciebie opinię "{{title}}"',
    'user.emails.feedback_updated'              => 'Wysłana przez Ciebie opinia "{{title}}" została zaktualizowana. W tej chwili posiada status "{{status}}"',
    'user.emails.feedback_validate-email'       => 'Potwierdź swój adres email, klikając na następujący link:',
    'user.emails.feedback_votes'                => 'Twoja opinia obecnie ma <strong>1</strong> głos. Zobacz Twoją opinię online:|Twoja opinia obecnie ma głosów - <strong>{{count}}</strong>. Zobacz Twoją opinię online:',
    'user.emails.greeting'                      => 'Szanowny(a) {{to_name}},',
    'user.emails.kb-explain'                    => 'Otrzymałeś tą wiadomość, ponieważ subskrybujesz naszą bazę wiedzy na <a href="{{ deskpro_url }}">{{ deskpro_url }}</a>.<br />Jeśli nie chcesz otrzymywać tych maili, <a href="{{ unsubscribe_url }}">Kliknij tutaj. </a>',
    'user.emails.kb-new-articles'               => '1 Nowy artykuł | {{count}} Nowych artykułów',
    'user.emails.kb-updated-articles'           => '1 Zaktualizowany artykuł | {{count}} zaktualizowanych artykułów',
    'user.emails.label_view-online'             => 'Zobacz online',
    'user.emails.message-clipped'               => '(Reszta wiadomości zostala ukryta)',
    'user.emails.password_agent-reset'          => 'Nasz pracownik zmienił Twoje hasło. Możesz się zalogować z tymi poświadczeniami.',
    'user.emails.password_reset'                => 'Poprosiłeś(aś) o zmianę hasła. Żeby zmienić hasło, kliknij na link poniżej:',
    'user.emails.profile_email-new-confirm'     => 'Żeby zakończyć dodanie wtórnego adresu email, kliknij na następujący link potwierdzający',
    'user.emails.profile_email_link-validate'   => 'Kliknij na następujący link, żeby potwierdzić swój adres email',
    'user.emails.register-agent-validation'     => 'Uwaga: Zanim twoje konto będzie w pełni aktywne, nasz agent musi ręcznie je sprawdzić. Zgłoszenia oraz inna zawartość, którą przesyłasz, zostanie zatrzymana w kolejce oczekujących na sprawdzenie do czasu sprawdzenia twojego konta.',
    'user.emails.register-confirm'              => 'Dziękujemy za rejestrację konta.<br /><br />Żeby zacząć używać swojego konta, potwierdź swój adres email klikając na następujący link',
    'user.emails.register-welcome'              => 'Dziękujemy za rejestrację. Możesz już się logować za pomocą adresu email {{to_email}} w systemie zgłoszeń.',
    'user.emails.registration_closed'           => 'Szanowny(a) {{name}},<br /><br /><br /><br />Nowe petycje mogą być wysłane tylko przez zarejestrowanych użytkowników Helpdesku. Jeżeli już masz konto, napisz do nas ze swojego zarejestrowanego adresu email.',
    'user.emails.reject_resolved'               => 'Twoja wiadomość nie została zaakceptowana ponieważ twoje zgłoszenie jest oznaczone jako rozwiązane. Konsultant nie przeczyta ani nie odpisze na tą wiadomość.',
    'user.emails.reject_resolved-new'           => 'Jeśli chcesz utworzyć nowe zgłoszenie, możesz wysłać nowy email do <a href="mailto:{{email_to}}">{{email_to}}</a> lub możesz wysłać przez formularz online dostępny na:<br /><a href="{{link}}">{{link}}</a>',
    'user.emails.reject_resolved-newemail'      => 'Jeśli chcesz utworzyć nowe zgłoszenie, możesz wysłać nowy email do <a href="mailto:{{email_to}}">{{email_to}}</a>',
    'user.emails.ticket_access_ticket_online'   => 'Zobacz i kieruj zgłoszeniem online:',
    'user.emails.ticket_cc-new'                 => 'Zostałeś dołączony do zgłoszenia, stworzonego przez {{name}}.',
    'user.emails.ticket_flood'                  => 'Niedawno wysłałeś(aś) email do naszego Helpdesku. Nasz system automatyczny wykrył, że wysłałeś(aś) wiele wiadomości w krótkim czasie, jak by były automatycznie zgenerowane.<br /><br />Żeby się zabezpieczyć przed pętlą automatycznych odpowiedzi, nasz Helpdesk nie będzie więcej wysyłał do Ciebie automatycznych powiadomień.',
    'user.emails.ticket_message_title'          => '{{date}} o {{time}}, {{author}} napisał(a):',
    'user.emails.ticket_no-autoresponse'        => 'Ostrzeżenie: emaile potwierdzające zostały wyłączone',
    'user.emails.ticket_rate-negative'          => 'Nie',
    'user.emails.ticket_rate-neutral'           => 'Co nieco',
    'user.emails.ticket_rate-positive'          => 'Tak',
    'user.emails.ticket_rate-question'          => 'Czy ta wiadomość była przydatna?',
    'user.emails.ticket_received'               => 'Twoja petycja została otrzymana. Jeden z naszych pracowników wkrótce na nią odpowie.',
    'user.emails.ticket_reply-confirm'          => 'Dziękujemy za Twoją odpowiedź. Jeden z naszych pracowników wkrótce na nią odpowie.',
    'user.emails.ticket_validate'               => 'Dziękujemy za Twoją wiadomość.<br /><br />Zanim nasi pracownicy przeczytają i odpowiedzą na nią, musisz potwierdzić swój adres email.',
    'user.emails.tickets_ommitted'              => '1 wiadomość została pominięta| Pominiętych wiadomości - {{count}}',
    'user.emails.view_full_history_online'      => 'Zobacz całą treść petycji online',
);
