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
    'agent.emails.account_rec_dm_by'                    => '{{account}} odebrało wiadomość od {{user}}',
    'agent.emails.account_rec_retweet_by'               => '{{account}} zostało retweetowane przez {{user}}',
    'agent.emails.account_rec_tweet_by'                 => '{{account}} odebrało tweeta od {{user}}',
    'agent.emails.admin_password_reset_error'           => 'Próbowałeś/aś zresetować twoje hasło. Jako zabezpieczenie, administratorzy nie mogą użyć interfejsu WWW, aby zresetować hasło.',
    'agent.emails.admin_password_reset_instruction'     => 'Zamiast tego, użyj narzędzia konsoli CMD z serwera terminali:',
    'agent.emails.agent_assigned_team_tweet'            => '{{agent}} prydzielił tweeta do twojego zespołu',
    'agent.emails.agent_assigned_you_tweet'             => '{{agent}} przydzielił tweeta do ciebie',
    'agent.emails.agent_completed_task_you_created'     => '{{agent}} zakończył zadanie  {{title}}, które utworzyłeś',
    'agent.emails.alert_failed_login'                   => 'Alarm o próbie błędnego logowania',
    'agent.emails.alert_successful_login'               => 'Alarm o udanym logowaniu',
    'agent.emails.change_password_instructions'         => 'Aby ustawić swoje nowe hasło,  zaloguj się do interfejsu agenta, następnie kliknij link "Preferencje" w górnym lewym rogu ekranu.',
    'agent.emails.deskpro_test_email'                   => 'Email testowy DeskPRO',
    'agent.emails.deskpro_test_email_body'              => 'To jest test systemu email DeskPRO.<br /><br />Jeśli otrzymałeś tą wiadomość email, Twoje ustawienia są poprawne.',
    'agent.emails.detected_as_forward'                  => 'Wykryto, iż wiadomość email, którą wysłałeś jest jest wiadomością przekazaną.',
    'agent.emails.email_change_merge'                   => 'Ta wiadomość email potwierdza, iż chcesz połączyć twoje dwa istniejące konta wsparcia: {{old_email}} oraz {{new_email}}.Jeśli nie chcesz scalić tych kont, lub jeśli tego nie zażądałeś, możesz zignorować tą wiadomość email. Aby potwierdzić scalanie kont, kliknij następujący link:',
    'agent.emails.error_missing_marker_explain'         => 'Ten znacznik odpowiedzi jest wymagany, aby prawidłowo przetwarzać twoje odpowiedzi. Wyślij ponownie twoją odpowiedź, ale tym razem nie zmieniaj linii znacznika.',
    'agent.emails.error_reply_to_ticket_missing_marker' => 'Twoja odpowiedź do zgłoszenia #{{ticket_id}} nie zawierała linii znacznika, która wygląda tak:',
    'agent.emails.error_unknown_email'                  => 'Twoja wiadomość została odrzucona, ponieważ została wysłana z nieznanego adresu email. Wysyłaj wiadomości email z adresu zarejestrowanego w swoim profilu agenta w dziale wsparcia.',
    'agent.emails.first_seen'                           => 'Widziane po raz pierwszy',
    'agent.emails.fwd_error_more_info'                  => 'Aby uzyskać więcej informacji na temat tego błędu, zajrzyj do strony wsparcia DeskPRO:<br/><a href="http://support.deskpro.com/kb/articles/106">http://support.deskpro.com/kb/articles/106</a>',
    'agent.emails.fwd_not_processed'                    => 'Z powodu tego błędu, twoja wiadomość email została odrzucona. Nie utworzono nowego Zgłoszenia oraz nie utworzono żadnych powiadomień oraz innych akcji. Jeśli musisz utworzyć nowe Zgłoszenie dla użytkownika, użyj interfejsu WWW agenta pod adresem <a href="{{url}}">{{url}}</a>',
    'agent.emails.fwd_resend_without_fwd'               => 'Jeśli NIE chcesz utworzyć nowego zgłoszenia w imieniu użytkownika, możesz spróbować ponownie wysłać tą wiadomość email bez znaków "DW:", "FW:" lub "FWD:" w temacie wiadomości. Ta czynność utworzy nowe zgłoszenie w twoim imieniu:',
    'agent.emails.invalid_fwd_email_address'            => 'Jakkolwiek nie mogliśmy <b>zweryfikować adresu email</b> użytkownika oryginalnej wiadomości. Sprawdź, czy twoja aplikacja do obsługi poczty email dodała adres klienta. Niektóre aplikacje do obsługi poczty email pokazują tylko nazwę lub imię, w takim przypadku możesz wpisać adres email ręcznie.',
    'agent.emails.invalid_fwd_email_parse'              => 'Jakkolwiek, nie mogliśmy <b>przetworzyć oryginalnej wiadomości</b>, więc nie utworzono nowego zgłosznia.',
    'agent.emails.invalid_fwd_try_attach'               => 'Jeśli twoja aplikacja do obsługi wiadomości email obsługuje przesyłanie wiadomości dalej jako załącznik zamiast przesyłania w treści, możesz spróbować to zrobić. Ta metoda przesyłania działa lepiej z DeskPRO.',
    'agent.emails.login_url'                            => 'Adres URL logowania',
    'agent.emails.name_reset_your_password'             => '{{name}} zresetował hasło do twojego konta agenta',
    'agent.emails.new_agent_reply_ticket_subject'       => '[#{{ticket.id}} ODPOWIEDŹ AGENTA] Odp: {{ticket.subject}}',
    'agent.emails.new_chat_message_from'                => 'Nowa wiadomość czat od {{name}}',
    'agent.emails.new_mention_ticket_subject'           => '[#{{ticket.id}} WSPOMNIANO] Odp: {{ticket.subject}}',
    'agent.emails.new_note_ticket_subject'              => '[#{{ticket.id}} NOTATKA] Odp: {{ticket.subject}}',
    'agent.emails.new_user_reply_ticket_subject'        => '[#{{ticket.id}} ODP. UŻYTKOWNIKA] Odp: {{ticket.subject}}',
    'agent.emails.newagent-about'                       => 'DeskPRO jest oprogramowaniem służącym do wsparcia, którego używa twoja organizacja.',
    'agent.emails.newagent-about-cloud'                 => 'DeskPRO jest oprogramowaniem pracującym w chmurze, służącym do wsparcia, którego używa twoja organizacja.',
    'agent.emails.newagent-about-cloud-demo'            => 'DeskPRO jest oprogramowaniem pracującym w chmurze, służącym do wsparcia, które testuje twoja organizacja.',
    'agent.emails.newagent-about-demo'                  => 'DeskPRO jest oprogramowaniem służącym do wsparcia, które testuje twoja organizacja.',
    'agent.emails.newagent-about-pdf'                   => 'Napisaliśmy szybki przewodnik do najważniejszych rzeczy jakie będziesz musiał wiedzieć jako Agent. Załączyliśmy go, "Getting Started with DeskPRO.pdf".',
    'agent.emails.newagent-created-account'             => 'Twój kolega {{admin_name}} ({{admin_email}}) utworzył konto DeskPRO dla ciebie na <a href="{{helpdesk_url}}">{{helpdesk_url}}</a>.',
    'agent.emails.newagent-help'                        => 'Jeśli potrzebujesz pomocy lub masz jakieś pytania, sprawdź stronę wsparcia DeskPRO <a href="https://support.deskpro.com/">https://support.deskpro.com/</a> lub skontaktuj się bezpośrednio pod adresem <a href="mailto:support@deskpro.com">support@deskpro.com</a>.',
    'agent.emails.newagent-login-initial-password'      => 'Twoje hasło początkowe: {{password}}',
    'agent.emails.newagent-login-link'                  => 'Możesz zalogować się tutaj: <a href="{{link}}">{{link}}</a>',
    'agent.emails.newagent-login-your-email'            => 'Twój adres email: {{email}}',
    'agent.emails.newagent-login-your-password'         => 'Notka: Już masz konto użytkownika na tej stronie wsparcia. Możesz zalogować się jako Agent, używając hasła ustawionego poprzednio.',
    'agent.emails.newagent-welcome-to-deskpro'          => 'Witamy w DeskPRO!',
    'agent.emails.notice_account_login'                 => 'Twoje konto zostało użyte do udanego logowania.',
    'agent.emails.notice_login_attempt'                 => 'Ktoś próbował się zalogować, używając twojego konta.',
    'agent.emails.person_replied'                       => '{{name}} odpowiedział na "{{subject}}"',
    'agent.emails.referring_page'                       => 'Strona Odwołująca',
    'agent.emails.reply_above_line'                     => 'ODPOWIEDZ POWYŻEJ',
    'agent.emails.subject_email_change_merge'           => 'Potwierdź zmianę adresu email oraz scalanie konta',
    'agent.emails.subject_error_forwarded_email'        => 'Błąd w przekazywaniu wiadomości: {{ subject }}',
    'agent.emails.subject_error_with_message'           => 'Błąd w wiadomości: {{ subject }}',
    'agent.emails.subject_error_with_reply'             => 'Błąd w twojej odpowiedzi do: {{ subject }}',
    'agent.emails.subject_new_agent'                    => 'Twoje nowe konto agenta wsparcia w DeskPRO',
    'agent.emails.subject_new_ticket'                   => '[#{{ticket.id}} NOWE ZGŁOSZENIE] {{ticket.subject}}',
    'agent.emails.subject_new_ticket_assigned'          => '[#{{ticket.id}} NOWE ZGŁOSZENIE + PRZYDZIAŁ] {{ticket.subject}}',
    'agent.emails.subject_new_ticket_assignedteam'      => '[#{{ticket.id}} NOWE ZGŁOSZENIE + PRZYDZIELONY ZESPÓŁ] {{ticket.subject}}',
    'agent.emails.task_assigned_due_today'              => 'Zadanie {{title}}, przypisane do ciebie, jest do wykonania dzisiaj.',
    'agent.emails.task_assigned_team_due_today'         => 'Zadanie {{title}}, przydzielone do twojego zespołu, jest do wykonania dzisiaj.',
    'agent.emails.task_assigned_to_you_by'              => 'Zadanie {{title}} przydzielone do ciebie przez {{agent}}',
    'agent.emails.task_assigned_to_your_team_by'        => 'Zadanie {{title}} przydzielone do twojego zespołu przez {{agent}}',
    'agent.emails.task_completed'                       => 'Zadanie {{title}} ukończono',
    'agent.emails.task_due_today'                       => 'Zadanie {{title}} do wykonania dzisiaj',
    'agent.emails.ticket_action_assigned'               => 'PRZYDZIELONE',
    'agent.emails.ticket_action_assigned_team'          => 'PRZYDZIELONY ZESPÓŁ',
    'agent.emails.ticket_action_deleted'                => 'USUNIĘTO',
    'agent.emails.ticket_action_followed'               => 'ŚLEDZONE',
    'agent.emails.ticket_action_spam'                   => 'SPAM',
    'agent.emails.ticket_action_status_agent'           => 'OCZEKUJE NA AGENTA',
    'agent.emails.ticket_action_status_resolved'        => 'ZAKOŃCZONE',
    'agent.emails.ticket_action_status_user'            => 'OCZEKUJE NA UŻYTKOWNIKA',
    'agent.emails.ticket_action_updated'                => 'ZAKTUALIZOWANO',
    'agent.emails.ticket_sla_failed'                    => 'NIEPOWODZENIE SLA',
    'agent.emails.ticket_sla_warning'                   => 'OSTRZEŻENIE SLA',
    'agent.emails.ticket_was_created'                   => '{{name}} utworzył nowe zgłoszenie',
    'agent.emails.tweet_assigned_to_you_by'             => 'Tweet przydzielony do ciebie przez {{agent}}',
    'agent.emails.updated_ticket_assigned'              => 'i przydzielił je do ciebie',
    'agent.emails.updated_ticket_assigned_team'         => 'and przydzielił do twojego zespołu {{team}}',
    'agent.emails.updated_ticket_followed'              => 'i dodał cię jako śledzącego',
    'agent.emails.user_ip'                              => 'IP użytkownika',
    'agent.emails.user_replied_to_tweet'                => '{{name}} odpowiedział na twój tweet',
    'agent.emails.user_replied_to_tweet_you_wrote'      => '{{name}}odpowiedział na tweet, który napisałeś do konta {{account}}.',
    'agent.emails.user_updated_ticket'                  => '{{name}} zaktualizował zgłoszenie "{{subject}}"',
    'agent.emails.view_account_tweets'                  => 'Zobacz tweety konta {{account}}\'',
    'agent.emails.view_delegated_tasks'                 => 'Zobacz swoje oddelegowane zadania',
    'agent.emails.view_online_at'                       => 'Zobacz online w:',
    'agent.emails.view_team_tweets'                     => 'Zobacz tweety swojego zespołu',
    'agent.emails.view_ticket_online'                   => 'Zobacz to zgłoszenie online',
    'agent.emails.view_your_tasks'                      => 'Zobacz swoje zadania',
    'agent.emails.view_your_teams_tasks'                => 'Zobacz zadania swojego zespołu',
    'agent.emails.view_your_tweets'                     => 'Zobacz swoje tweety',
    'agent.emails.your_initial_password'                => 'Twoje hasło początkowe',
);
