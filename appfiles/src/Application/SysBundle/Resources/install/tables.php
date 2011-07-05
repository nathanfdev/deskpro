$queries[] = "CREATE TABLE sessions (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, auth VARCHAR(15) NOT NULL, data LONGTEXT NOT NULL, is_person TINYINT(1) NOT NULL, active_status VARCHAR(15) NOT NULL, page_count INT NOT NULL, date_created DATETIME NOT NULL, date_last DATETIME NOT NULL, INDEX IDX_9A609D13217BBB47 (person_id), INDEX IDX_9A609D1370BEE6D (visitor_id), INDEX date_last_idx (date_last, is_person), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE people (id INT AUTO_INCREMENT NOT NULL, picture_blob_id INT DEFAULT NULL, locale_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, primary_email_id INT DEFAULT NULL, gravatar_url LONGTEXT NOT NULL, is_contact TINYINT(1) NOT NULL, is_user TINYINT(1) NOT NULL, is_agent TINYINT(1) NOT NULL, is_confirmed TINYINT(1) NOT NULL, is_agent_confirmed TINYINT(1) NOT NULL, importance SMALLINT NOT NULL, name LONGTEXT NOT NULL, first_name LONGTEXT DEFAULT NULL, last_name LONGTEXT DEFAULT NULL, secret_string VARCHAR(40) NOT NULL, organization_position VARCHAR(100) NOT NULL, timezone VARCHAR(50) NOT NULL, password VARCHAR(40) DEFAULT NULL, salt VARCHAR(40) NOT NULL, date_created DATETIME NOT NULL, date_last_login DATETIME DEFAULT NULL, date_picture_check DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_28166A26F0187A77 (picture_blob_id), INDEX IDX_28166A26E559DFD1 (locale_id), INDEX IDX_28166A2632C8A3DE (organization_id), UNIQUE INDEX UNIQ_28166A26894DAC38 (primary_email_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE person2usergroups (person_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_356C969E217BBB47 (person_id), INDEX IDX_356C969ED2112630 (usergroup_id), PRIMARY KEY(person_id, usergroup_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE visitors (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, auth VARCHAR(15) NOT NULL, ip_address VARCHAR(80) NOT NULL, user_agent VARCHAR(255) NOT NULL, ref_page VARCHAR(255) NOT NULL, landing_page VARCHAR(255) NOT NULL, last_page VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, date_last DATETIME NOT NULL, INDEX IDX_7B74A43F217BBB47 (person_id), INDEX date_last_idx (date_last), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE blobs (id INT AUTO_INCREMENT NOT NULL, original_blob_id INT DEFAULT NULL, save_path VARCHAR(255) DEFAULT NULL, filename VARCHAR(120) NOT NULL, filesize INT NOT NULL, content_type VARCHAR(50) NOT NULL, authcode VARCHAR(20) NOT NULL, is_media_upload TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, dim_w INT NOT NULL, dim_h INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_896C3E356BBE2052 (original_blob_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE locales (id INT AUTO_INCREMENT NOT NULL, language_id INT DEFAULT NULL, locale VARCHAR(20) NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_E59B54BB82F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE organizations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, importance SMALLINT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE people_emails (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, email_domain VARCHAR(255) NOT NULL, is_validated TINYINT(1) NOT NULL, comment TINYTEXT NOT NULL, date_created DATETIME NOT NULL, date_validated DATETIME DEFAULT NULL, INDEX IDX_3A96CAB8217BBB47 (person_id), INDEX email_domain_idx (email_domain), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_people (person_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_C37D5395217BBB47 (person_id), PRIMARY KEY(person_id, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE custom_data_person (person_id INT NOT NULL, field_id INT NOT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX IDX_621E55A5217BBB47 (person_id), INDEX IDX_621E55A5443707B0 (field_id), INDEX field_id_idx (field_id, person_id), PRIMARY KEY(person_id, field_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE people_contact_data (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, handler_class VARCHAR(80) NOT NULL, comment VARCHAR(100) NOT NULL, field_1 LONGTEXT NOT NULL, field_2 LONGTEXT NOT NULL, field_3 LONGTEXT NOT NULL, field_4 LONGTEXT NOT NULL, field_5 LONGTEXT NOT NULL, field_6 LONGTEXT NOT NULL, field_7 LONGTEXT NOT NULL, field_8 LONGTEXT NOT NULL, field_9 LONGTEXT NOT NULL, field_10 LONGTEXT NOT NULL, INDEX IDX_14604ED8217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE usergroups (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, note LONGTEXT NOT NULL, is_agent_group TINYINT(1) NOT NULL, sys_name VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE people_prefs (person_id INT NOT NULL, name VARCHAR(255) NOT NULL, value_str LONGTEXT DEFAULT NULL, value_array LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_8112E0E9217BBB47 (person_id), PRIMARY KEY(person_id, name)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE person_usersource_assoc (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, usersource_id INT DEFAULT NULL, identity VARCHAR(255) NOT NULL, identity_friendly VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, last_used_at DATETIME NOT NULL, INDEX IDX_72215949217BBB47 (person_id), UNIQUE INDEX UNIQ_722159495B71BD01 (usersource_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE person_scraper_assoc (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, person_scraper_id INT NOT NULL, identity VARCHAR(255) DEFAULT NULL, raw_data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, remote_created_at DATETIME DEFAULT NULL, remote_updated_at DATETIME DEFAULT NULL, INDEX IDX_945589A7217BBB47 (person_id), UNIQUE INDEX UNIQ_945589A76CD3D8EC (person_scraper_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_accounts (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT DEFAULT NULL, oauth_token VARCHAR(4000) NOT NULL, oauth_token_secret VARCHAR(4000) NOT NULL, UNIQUE INDEX UNIQ_D4051D30A76ED395 (user_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_accounts_person (account_id BIGINT NOT NULL, person_id INT NOT NULL, INDEX IDX_BB12235C9B6B5FBA (account_id), INDEX IDX_BB12235C217BBB47 (person_id), PRIMARY KEY(account_id, person_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_statuses_notes (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, person_id INT DEFAULT NULL, text VARCHAR(4000) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_68AD62906BF700BD (status_id), INDEX IDX_68AD6290217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, assigned_agent_id INT DEFAULT NULL, assigned_agent_team_id INT DEFAULT NULL, is_completed TINYINT(1) NOT NULL, title LONGTEXT NOT NULL, visibility INT NOT NULL, date_due DATE DEFAULT NULL, date_created DATETIME NOT NULL, date_completed DATETIME DEFAULT NULL, INDEX IDX_50586597217BBB47 (person_id), INDEX IDX_5058659749197702 (assigned_agent_id), INDEX IDX_50586597410D1341 (assigned_agent_team_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE task_comments (id INT AUTO_INCREMENT NOT NULL, task_id INT NOT NULL, person_id INT DEFAULT NULL, content LONGTEXT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_1F5E7C668DB60186 (task_id), INDEX IDX_1F5E7C66217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE task_associations (id INT AUTO_INCREMENT NOT NULL, task_id INT DEFAULT NULL, person_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_41B0E09C8DB60186 (task_id), INDEX IDX_41B0E09C217BBB47 (person_id), INDEX IDX_41B0E09C700047D2 (ticket_id), INDEX IDX_41B0E09C32C8A3DE (organization_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE languages (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_A0D15379727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE agent_access (person_id INT NOT NULL, access_agent TINYINT(1) NOT NULL, access_admin TINYINT(1) NOT NULL, access_billing TINYINT(1) NOT NULL, access_reports TINYINT(1) NOT NULL, PRIMARY KEY(person_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE agent_department_members (person_id INT NOT NULL, department_id INT NOT NULL, INDEX IDX_312E004F217BBB47 (person_id), INDEX IDX_312E004FAE80F5DF (department_id), PRIMARY KEY(person_id, department_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE agent_notifications (filter_id INT NOT NULL, person_id INT NOT NULL, notify_type VARCHAR(50) NOT NULL, INDEX IDX_6CC0E764D395B25E (filter_id), INDEX IDX_6CC0E764217BBB47 (person_id), PRIMARY KEY(filter_id, person_id, notify_type)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE agent_teams (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE agent_team_members (team_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_CC952C03296CD8AE (team_id), INDEX IDX_CC952C03217BBB47 (person_id), PRIMARY KEY(team_id, person_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE api_auth_codes (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, code VARCHAR(50) NOT NULL, scope VARCHAR(250) DEFAULT NULL, redirect_url VARCHAR(250) DEFAULT NULL, date_created DATETIME NOT NULL, date_expires DATETIME NOT NULL, INDEX IDX_EE25EC29217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE api_auth_tokens (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, token VARCHAR(50) NOT NULL, scope VARCHAR(250) DEFAULT NULL, date_created DATETIME NOT NULL, date_expires DATETIME NOT NULL, INDEX IDX_E08E3CE2217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE api_keys (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, code VARCHAR(25) NOT NULL, note LONGTEXT NOT NULL, INDEX IDX_9579321F217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE articles (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, language_id INT DEFAULT NULL, markup_mode VARCHAR(15) NOT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, excerpt VARCHAR(1000) NOT NULL, content LONGTEXT NOT NULL, view_count INT NOT NULL, total_rating INT NOT NULL, num_ratings INT NOT NULL, status VARCHAR(15) NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, display_order INT NOT NULL, date_created DATETIME NOT NULL, date_published DATETIME DEFAULT NULL, date_end DATETIME DEFAULT NULL, end_action VARCHAR(10) DEFAULT NULL, INDEX IDX_BFDD3168217BBB47 (person_id), INDEX IDX_BFDD316882F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_to_product (article_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_610BE8D97294869C (article_id), INDEX IDX_610BE8D94584665A (product_id), PRIMARY KEY(article_id, product_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_to_categories (article_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_9A1B4BB07294869C (article_id), INDEX IDX_9A1B4BB012469DE2 (category_id), PRIMARY KEY(article_id, category_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, is_agent TINYINT(1) NOT NULL, is_book TINYINT(1) NOT NULL, template_suffix VARCHAR(100) DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_62A97E9727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E65C1B50D2112630 (usergroup_id), INDEX IDX_E65C1B5012469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_comments (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_A7662417294869C (article_id), INDEX IDX_A766241217BBB47 (person_id), INDEX IDX_A76624170BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_pending_create (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, comment VARCHAR(1000) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_27A971C3217BBB47 (person_id), INDEX IDX_27A971C3700047D2 (ticket_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_ratings (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, rating INT NOT NULL, comment VARCHAR(2500) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_2364437E7294869C (article_id), INDEX IDX_2364437E217BBB47 (person_id), INDEX IDX_2364437E70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_revisions (article_id INT NOT NULL, person_id INT DEFAULT NULL, content LONGTEXT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_538472A1217BBB47 (person_id), PRIMARY KEY(article_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE article_validating_edits (article_id INT NOT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, excerpt VARCHAR(1000) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_40983882217BBB47 (person_id), PRIMARY KEY(article_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ban_emails (banned_email VARCHAR(255) NOT NULL, PRIMARY KEY(banned_email)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ban_ips (banned_ip VARCHAR(100) NOT NULL, ip_start BIGINT NOT NULL, ip_end BIGINT NOT NULL, PRIMARY KEY(banned_ip)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE blob_object_attach (object_type VARCHAR(100) NOT NULL, blob_id INT DEFAULT NULL, object_id INT NOT NULL, INDEX IDX_D048A866ED3E8EA5 (blob_id), PRIMARY KEY(object_type)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE blobs_storage (id INT AUTO_INCREMENT NOT NULL, blob_id INT NOT NULL, data LONGTEXT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE cache (id VARCHAR(100) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date_expire DATETIME DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE chat_conversations (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, person_id INT DEFAULT NULL, session_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, status VARCHAR(15) NOT NULL, person_name VARCHAR(255) NOT NULL, person_email VARCHAR(255) NOT NULL, is_agent TINYINT(1) NOT NULL, is_window TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, date_assigned DATETIME DEFAULT NULL, date_first_agent_message DATETIME DEFAULT NULL, date_ended DATETIME DEFAULT NULL, INDEX IDX_5813432EAE80F5DF (department_id), INDEX IDX_5813432E3414710B (agent_id), INDEX IDX_5813432E217BBB47 (person_id), INDEX IDX_5813432E613FECDF (session_id), INDEX IDX_5813432E70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE chat_conversation_to_person (conversation_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_1CA5AE439AC0396 (conversation_id), INDEX IDX_1CA5AE43217BBB47 (person_id), PRIMARY KEY(conversation_id, person_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE chat_messages (id INT AUTO_INCREMENT NOT NULL, conversation_id INT DEFAULT NULL, author_id INT DEFAULT NULL, person_name VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, is_sys TINYINT(1) NOT NULL, is_user_hidden TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_EF20C9A69AC0396 (conversation_id), INDEX IDX_EF20C9A6F675F31B (author_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE chat_quick_replies (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, is_global TINYINT(1) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_30AB1C31217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE client_channel_subscriptions (id INT AUTO_INCREMENT NOT NULL, session_id INT DEFAULT NULL, channel VARCHAR(255) NOT NULL, private_channel_id VARCHAR(150) DEFAULT NULL, date_ping DATETIME NOT NULL, INDEX IDX_2F1F0299613FECDF (session_id), INDEX date_ping (date_ping), INDEX channel (channel), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE client_messages (id INT AUTO_INCREMENT NOT NULL, for_person_id INT DEFAULT NULL, channel VARCHAR(255) NOT NULL, auth VARCHAR(15) NOT NULL, handler_class VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_by_client VARCHAR(255) NOT NULL, for_client VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_F5E42E53D2872966 (for_person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE content_search (object_type VARCHAR(100) NOT NULL, object_id INT NOT NULL, id INT NOT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, content LONGTEXT NOT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_DCC6AB0B217BBB47 (person_id), INDEX IDX_DCC6AB0B70BEE6D (visitor_id), PRIMARY KEY(object_type, object_id, id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE content_search_attribute (object_type VARCHAR(100) NOT NULL, object_id INT NOT NULL, attribute_id VARCHAR(200) NOT NULL, content VARCHAR(200) NOT NULL, id INT NOT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_F95D8EA1217BBB47 (person_id), INDEX IDX_F95D8EA170BEE6D (visitor_id), PRIMARY KEY(object_type, object_id, attribute_id, content, id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE custom_data_organizations (organization_id INT NOT NULL, field_id INT NOT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX IDX_20C5B8AC32C8A3DE (organization_id), INDEX IDX_20C5B8AC443707B0 (field_id), INDEX field_id_idx (field_id, organization_id), PRIMARY KEY(organization_id, field_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE custom_data_ticket (ticket_id INT NOT NULL, field_id INT NOT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX IDX_C1622970700047D2 (ticket_id), INDEX IDX_C1622970443707B0 (field_id), INDEX field_id_idx (field_id, ticket_id), PRIMARY KEY(ticket_id, field_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE custom_def_organizations (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, plugin_id VARCHAR(255) DEFAULT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_240601E7727ACA70 (parent_id), UNIQUE INDEX UNIQ_240601E7EC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE custom_def_people (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, plugin_id VARCHAR(255) DEFAULT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_4840CFDA727ACA70 (parent_id), UNIQUE INDEX UNIQ_4840CFDAEC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE custom_def_ticket (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, plugin_id VARCHAR(255) DEFAULT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_F7F6085F727ACA70 (parent_id), UNIQUE INDEX UNIQ_F7F6085FEC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE departments (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, INDEX IDX_16AEB8D4727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE downloads (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, person_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, num_downloads INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_4B73A4B512469DE2 (category_id), INDEX IDX_4B73A4B5217BBB47 (person_id), INDEX IDX_4B73A4B5ED3E8EA5 (blob_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE download_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_3317F15727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE download_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_1E2B566ED2112630 (usergroup_id), INDEX IDX_1E2B566E12469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE email_from (id INT AUTO_INCREMENT NOT NULL, name TINYTEXT NOT NULL, address TINYTEXT NOT NULL, transport_class VARCHAR(80) NOT NULL, transport_options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE email_gateways (id INT AUTO_INCREMENT NOT NULL, name TINYTEXT NOT NULL, address TINYTEXT NOT NULL, connection_class VARCHAR(80) NOT NULL, connection_options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', processor_class VARCHAR(80) NOT NULL, is_enabled TINYINT(1) NOT NULL, date_last_login DATETIME DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE email_gateway_logs (id INT AUTO_INCREMENT NOT NULL, gateway_id INT DEFAULT NULL, plugin_id VARCHAR(255) DEFAULT NULL, message VARCHAR(1000) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', log_type VARCHAR(50) NOT NULL, date_created DATETIME NOT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_C6F1CFC1577F8E00 (gateway_id), UNIQUE INDEX UNIQ_C6F1CFC1EC942BCF (plugin_id), INDEX log_type_idx (log_type), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE email_sources (id INT AUTO_INCREMENT NOT NULL, gateway_id INT DEFAULT NULL, object_type VARCHAR(50) NOT NULL, object_id INT NOT NULL, headers VARCHAR(1000) NOT NULL, status VARCHAR(15) NOT NULL, save_path VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_6F9D0D3D577F8E00 (gateway_id), INDEX object_idx (object_type, object_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE email_sources_blobs (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, data LONGTEXT NOT NULL, INDEX IDX_4F81B70F953C1C61 (source_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE error_logs (id INT AUTO_INCREMENT NOT NULL, plugin_id VARCHAR(255) DEFAULT NULL, message VARCHAR(1000) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', log_type VARCHAR(50) NOT NULL, date_created DATETIME NOT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_B4F80B60EC942BCF (plugin_id), INDEX log_type_idx (log_type), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE glossary_words (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ideas (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, status_category_id INT DEFAULT NULL, category_id INT DEFAULT NULL, first_comment_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, status VARCHAR(15) NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, num_votes INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_1DB2F1DE217BBB47 (person_id), INDEX IDX_1DB2F1DE169CE813 (status_category_id), INDEX IDX_1DB2F1DE12469DE2 (category_id), UNIQUE INDEX UNIQ_1DB2F1DE69F11C14 (first_comment_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE idea_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_E4FA1F8F727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE idea_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_750A0C12D2112630 (usergroup_id), INDEX IDX_750A0C1212469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE idea_comments (id INT AUTO_INCREMENT NOT NULL, idea_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_25B753935B6FEF7D (idea_id), INDEX IDX_25B75393217BBB47 (person_id), INDEX IDX_25B7539370BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE idea_status_categories (id INT AUTO_INCREMENT NOT NULL, status_type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE idea_votes (id INT AUTO_INCREMENT NOT NULL, idea_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, num_votes INT NOT NULL, is_returned TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_104C491A5B6FEF7D (idea_id), INDEX IDX_104C491A217BBB47 (person_id), INDEX IDX_104C491A70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_articles (article_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_2F30AF707294869C (article_id), PRIMARY KEY(article_id, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_blobs (blob_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_EC63B2F0ED3E8EA5 (blob_id), PRIMARY KEY(blob_id, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE label_defs (label_type VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(label_type, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_downloads (download_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_588FD17DC667AEAB (download_id), PRIMARY KEY(download_id, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_ideas (idea_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_78BD7D1B5B6FEF7D (idea_id), PRIMARY KEY(idea_id, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_news (news_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_A2869A08B5A459A0 (news_id), PRIMARY KEY(news_id, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_organizations (organization_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_9F089F4232C8A3DE (organization_id), PRIMARY KEY(organization_id, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_tasks (label VARCHAR(255) NOT NULL, task_id INT NOT NULL, INDEX IDX_3557E9528DB60186 (task_id), PRIMARY KEY(label, task_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE labels_tickets (ticket_id INT NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_6C514FB700047D2 (ticket_id), PRIMARY KEY(ticket_id, label)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE log_items (id INT AUTO_INCREMENT NOT NULL, log_name VARCHAR(50) NOT NULL, session_name VARCHAR(100) DEFAULT NULL, flag VARCHAR(50) DEFAULT NULL, priority INT NOT NULL, priority_name VARCHAR(25) NOT NULL, message VARCHAR(1000) NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', date_created DATETIME NOT NULL, INDEX log_name_idx (log_name, session_name), INDEX flag_idx (flag), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, is_published TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_1DD3995012469DE2 (category_id), INDEX IDX_1DD39950217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE news_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_D68C9111727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE news_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_513C43F9D2112630 (usergroup_id), INDEX IDX_513C43F912469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE news_comments (id INT AUTO_INCREMENT NOT NULL, news_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_16A0357BB5A459A0 (news_id), INDEX IDX_16A0357B217BBB47 (person_id), INDEX IDX_16A0357B70BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE organizations_contact_data (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, handler_class VARCHAR(80) NOT NULL, comment VARCHAR(100) NOT NULL, field_1 LONGTEXT NOT NULL, field_2 LONGTEXT NOT NULL, field_3 LONGTEXT NOT NULL, field_4 LONGTEXT NOT NULL, field_5 LONGTEXT NOT NULL, field_6 LONGTEXT NOT NULL, field_7 LONGTEXT NOT NULL, field_8 LONGTEXT NOT NULL, field_9 LONGTEXT NOT NULL, field_10 LONGTEXT NOT NULL, INDEX IDX_25B60D5032C8A3DE (organization_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE organization_notes (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, date_created DATETIME NOT NULL, note VARCHAR(255) NOT NULL, INDEX IDX_8F9C404B32C8A3DE (organization_id), INDEX IDX_8F9C404B3414710B (agent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE permissions_cache (name VARCHAR(255) NOT NULL, usergroup_key VARCHAR(32) NOT NULL, usergroup_ids VARCHAR(1000) NOT NULL, perms LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', PRIMARY KEY(name, usergroup_key)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE person_display_elements (id INT AUTO_INCREMENT NOT NULL, display_zone VARCHAR(50) NOT NULL, element_type VARCHAR(50) NOT NULL, element_id INT NOT NULL, initial_state VARCHAR(50) NOT NULL, conds_all LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', conds_any LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', display_order INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE person_logs (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, action_type VARCHAR(255) NOT NULL, details LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date_created DATETIME NOT NULL, INDEX IDX_7A694277217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE people_notes (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, date_created DATETIME NOT NULL, note VARCHAR(255) NOT NULL, INDEX IDX_CA78DCCC217BBB47 (person_id), INDEX IDX_CA78DCCC3414710B (agent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE person_scraper (id INT AUTO_INCREMENT NOT NULL, usersource_id INT DEFAULT NULL, poll_interval INT DEFAULT NULL, poll_discovery_interval INT DEFAULT NULL, handler_class VARCHAR(255) NOT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', is_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1EEA1FDE5B71BD01 (usersource_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE person_stream (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, action_type VARCHAR(40) NOT NULL, summary VARCHAR(255) NOT NULL, details LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_6C4C8EAA217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE phrases (id INT AUTO_INCREMENT NOT NULL, language_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, groupname VARCHAR(255) DEFAULT NULL, phrase LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_121AC8C682F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE plugins (id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, version VARCHAR(100) NOT NULL, package_class VARCHAR(255) NOT NULL, package_class_file VARCHAR(255) NOT NULL, resources_path VARCHAR(255) NOT NULL, autoload_paths LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date_created DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE plugin_listeners (id INT AUTO_INCREMENT NOT NULL, plugin_id VARCHAR(255) DEFAULT NULL, event_name VARCHAR(255) DEFAULT NULL, event_options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', description VARCHAR(255) NOT NULL, run_order INT NOT NULL, listener_class VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_FEEE2572EC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, root INT DEFAULT NULL, depth INT NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, INDEX IDX_B3BA5A5A727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE queue_items (id INT AUTO_INCREMENT NOT NULL, groupname VARCHAR(255) DEFAULT NULL, priority INT NOT NULL, delay_until DATETIME NOT NULL, ttr INT NOT NULL, is_ready TINYINT(1) NOT NULL, is_dataonly TINYINT(1) NOT NULL, is_ignored TINYINT(1) NOT NULL, reserved_at DATETIME NOT NULL, timeout_at DATETIME NOT NULL, created_at DATETIME NOT NULL, data LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ratings_article (article_id INT NOT NULL, person_id INT DEFAULT NULL, ip_address VARCHAR(50) NOT NULL, rating INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_4C8A2B5B217BBB47 (person_id), PRIMARY KEY(article_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ratings_idea (idea_id INT NOT NULL, person_id INT DEFAULT NULL, ip_address VARCHAR(50) NOT NULL, rating INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_BAD6702F217BBB47 (person_id), PRIMARY KEY(idea_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE related_content (object_type VARCHAR(100) NOT NULL, object_id INT NOT NULL, rel_object_type VARCHAR(100) NOT NULL, rel_object_id INT NOT NULL, PRIMARY KEY(object_type, object_id, rel_object_type, rel_object_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE result_cache (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, criteria LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', results LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', extra LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', num_results INT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_D0B33C6B217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE search_term_boosters (object_type VARCHAR(100) NOT NULL, object_id INT NOT NULL, is_user TINYINT(1) NOT NULL, boosted_terms VARCHAR(255) NOT NULL, PRIMARY KEY(object_type, object_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE sendmail_queue (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, to_address VARCHAR(255) NOT NULL, attempts INT NOT NULL, date_created DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE sendmail_queue_part (id INT AUTO_INCREMENT NOT NULL, sendmail_queue_id INT DEFAULT NULL, data LONGTEXT NOT NULL, INDEX IDX_325FF8F4123DB51B (sendmail_queue_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, groupname VARCHAR(255) DEFAULT NULL, value LONGTEXT DEFAULT NULL, default_value LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE styles (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, note LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_B65AFAF5727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE style_resources (id INT AUTO_INCREMENT NOT NULL, style_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, resource LONGTEXT DEFAULT NULL, raw_resource LONGTEXT DEFAULT NULL, resource_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', user_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_43FBC036BACD6074 (style_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE style_resource_css (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, resource LONGTEXT DEFAULT NULL, raw_resource LONGTEXT DEFAULT NULL, resource_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', user_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE templates (id INT AUTO_INCREMENT NOT NULL, style_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, template LONGTEXT NOT NULL, template_compiled LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6F287D8EBACD6074 (style_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tickets (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, category_id INT DEFAULT NULL, priority_id INT DEFAULT NULL, workflow_id INT DEFAULT NULL, product_id INT DEFAULT NULL, person_id INT DEFAULT NULL, person_email_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, agent_team_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, email_gateway_id INT DEFAULT NULL, locked_by_agent INT DEFAULT NULL, ref VARCHAR(25) NOT NULL, auth VARCHAR(20) NOT NULL, creation_system VARCHAR(20) NOT NULL, status VARCHAR(15) NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, urgency INT NOT NULL, date_created DATETIME NOT NULL, date_resolved DATETIME DEFAULT NULL, date_closed DATETIME DEFAULT NULL, date_first_agent_reply DATETIME DEFAULT NULL, date_last_agent_reply DATETIME DEFAULT NULL, date_last_user_reply DATETIME DEFAULT NULL, date_agent_waiting DATETIME DEFAULT NULL, date_user_waiting DATETIME DEFAULT NULL, total_user_waiting INT NOT NULL, total_to_first_reply INT NOT NULL, date_locked DATETIME DEFAULT NULL, has_attachments TINYINT(1) NOT NULL, subject VARCHAR(255) NOT NULL, INDEX IDX_54469DF4AE80F5DF (department_id), INDEX IDX_54469DF412469DE2 (category_id), INDEX IDX_54469DF4497B19F9 (priority_id), INDEX IDX_54469DF42C7C2CBA (workflow_id), INDEX IDX_54469DF44584665A (product_id), INDEX IDX_54469DF4217BBB47 (person_id), INDEX IDX_54469DF43C7464FE (person_email_id), INDEX IDX_54469DF43414710B (agent_id), INDEX IDX_54469DF4FB3FBA04 (agent_team_id), INDEX IDX_54469DF432C8A3DE (organization_id), INDEX IDX_54469DF4FBCC7CDF (email_gateway_id), INDEX IDX_54469DF4428359E2 (locked_by_agent), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_access_codes (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, person_id INT DEFAULT NULL, auth VARCHAR(20) NOT NULL, INDEX IDX_CCEE41B5700047D2 (ticket_id), INDEX IDX_CCEE41B5217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tickets_attachments (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, person_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, message_id INT DEFAULT NULL, INDEX IDX_F06B468D700047D2 (ticket_id), INDEX IDX_F06B468D217BBB47 (person_id), INDEX IDX_F06B468DED3E8EA5 (blob_id), INDEX IDX_F06B468D537A1329 (message_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, INDEX IDX_AC60D43C727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tickets_deleted (ticket_id INT NOT NULL, new_ticket_id INT NOT NULL, by_person_id INT DEFAULT NULL, date_created DATETIME NOT NULL, reason VARCHAR(1000) NOT NULL, INDEX IDX_7EDF2278B5BE2AA2 (by_person_id), PRIMARY KEY(ticket_id, new_ticket_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_feedback (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, message_id INT DEFAULT NULL, person_id INT DEFAULT NULL, rating INT NOT NULL, message LONGTEXT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_5740B8D9700047D2 (ticket_id), INDEX IDX_5740B8D9537A1329 (message_id), INDEX IDX_5740B8D9217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_filters (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, agent_team_id INT DEFAULT NULL, is_global TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, is_enabled TINYINT(1) NOT NULL, sys_name VARCHAR(50) DEFAULT NULL, terms LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', group_by VARCHAR(255) NOT NULL, order_by VARCHAR(255) NOT NULL, INDEX IDX_74BB3EDF217BBB47 (person_id), INDEX IDX_74BB3EDFFB3FBA04 (agent_team_id), UNIQUE INDEX sys_name_unique (sys_name), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_filters_perms (id INT AUTO_INCREMENT NOT NULL, filter_id INT DEFAULT NULL, object_type VARCHAR(50) NOT NULL, object_id INT NOT NULL, UNIQUE INDEX UNIQ_93E8D427D395B25E (filter_id), INDEX object_idx (object_type, object_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tickets_flagged (ticket_id INT NOT NULL, person_id INT NOT NULL, color VARCHAR(20) NOT NULL, PRIMARY KEY(ticket_id, person_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tickets_logs (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, person_id INT DEFAULT NULL, action_type VARCHAR(40) NOT NULL, details LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date_created DATETIME NOT NULL, INDEX IDX_F5F41081700047D2 (ticket_id), INDEX IDX_F5F41081217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_macros (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, labels VARCHAR(1000) NOT NULL, is_enabled TINYINT(1) NOT NULL, is_global TINYINT(1) NOT NULL, actions LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_8E373A2C217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_macros_perms (id INT AUTO_INCREMENT NOT NULL, macro_id INT DEFAULT NULL, object_type VARCHAR(50) NOT NULL, object_id INT NOT NULL, UNIQUE INDEX UNIQ_EAB2E6D5F43A187E (macro_id), INDEX object_idx (object_type, object_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tickets_messages (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, person_id INT DEFAULT NULL, date_created DATETIME NOT NULL, is_agent_note TINYINT(1) NOT NULL, message_hash VARCHAR(40) NOT NULL, message LONGTEXT NOT NULL, INDEX IDX_3A9962E2700047D2 (ticket_id), INDEX IDX_3A9962E2217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_page_display (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, zone VARCHAR(50) NOT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', section VARCHAR(50) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_3667659DAE80F5DF (department_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tickets_participants (ticket_id INT NOT NULL, person_id INT NOT NULL, access_code_id INT DEFAULT NULL, person_email_id INT DEFAULT NULL, default_on TINYINT(1) NOT NULL, INDEX IDX_8D675752700047D2 (ticket_id), INDEX IDX_8D675752217BBB47 (person_id), UNIQUE INDEX UNIQ_8D675752EFFF2402 (access_code_id), INDEX IDX_8D6757523C7464FE (person_email_id), PRIMARY KEY(ticket_id, person_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_priorities (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, priority INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_triggers (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, event_trigger VARCHAR(50) NOT NULL, event_trigger_option VARCHAR(255) NOT NULL, is_enabled TINYINT(1) NOT NULL, terms LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', actions LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', sys_name VARCHAR(50) DEFAULT NULL, has_urgency TINYINT(1) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE ticket_workflows (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE tmp_data (id INT AUTO_INCREMENT NOT NULL, auth VARCHAR(15) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date_created DATETIME NOT NULL, date_expire DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_accounts_followers (id BIGINT AUTO_INCREMENT NOT NULL, account_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, INDEX IDX_EB8452969B6B5FBA (account_id), INDEX IDX_EB845296A76ED395 (user_id), UNIQUE INDEX account_user_idx (account_id, user_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_accounts_friends (id BIGINT AUTO_INCREMENT NOT NULL, account_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, INDEX IDX_FADA774D9B6B5FBA (account_id), INDEX IDX_FADA774DA76ED395 (user_id), UNIQUE INDEX account_user_idx (account_id, user_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_accounts_searches (id BIGINT AUTO_INCREMENT NOT NULL, account_id BIGINT DEFAULT NULL, term VARCHAR(255) NOT NULL, INDEX IDX_5CC0E8CF9B6B5FBA (account_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_statuses (id BIGINT NOT NULL, user_id BIGINT DEFAULT NULL, in_reply_to_status_id BIGINT DEFAULT NULL, retweet_id BIGINT DEFAULT NULL, in_reply_to_user_id BIGINT DEFAULT NULL, recipient_id BIGINT DEFAULT NULL, text VARCHAR(4000) NOT NULL, is_truncated TINYINT(1) NOT NULL, is_favorited TINYINT(1) NOT NULL, is_archived TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, geo_latitude NUMERIC(10, 5) DEFAULT NULL, geo_longitude NUMERIC(10, 5) DEFAULT NULL, source VARCHAR(4000) DEFAULT NULL, INDEX IDX_553D9D8DA76ED395 (user_id), INDEX IDX_553D9D8D6B347969 (in_reply_to_status_id), INDEX IDX_553D9D8D72A1C5CA (retweet_id), INDEX IDX_553D9D8DD2347268 (in_reply_to_user_id), INDEX IDX_553D9D8DE92F8F78 (recipient_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_statuses_long (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, text VARCHAR(4000) NOT NULL, is_public TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, date_read DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8B914BFB6BF700BD (status_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_statuses_mentions (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, starts INT NOT NULL, ends INT NOT NULL, INDEX IDX_66912DD16BF700BD (status_id), INDEX IDX_66912DD1A76ED395 (user_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_statuses_tags (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, hash VARCHAR(255) NOT NULL, starts INT NOT NULL, ends INT NOT NULL, INDEX IDX_DFBA76B56BF700BD (status_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_statuses_urls (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, url VARCHAR(255) NOT NULL, starts INT NOT NULL, ends INT NOT NULL, INDEX IDX_9A92D5326BF700BD (status_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE twitter_users (id BIGINT NOT NULL, name VARCHAR(40) NOT NULL, screen_name VARCHAR(20) NOT NULL, profile_image_url VARCHAR(200) NOT NULL, language VARCHAR(3) NOT NULL, is_protected TINYINT(1) NOT NULL, is_verified TINYINT(1) NOT NULL, location VARCHAR(255) DEFAULT NULL, is_geo_enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE usergroup_properties (id INT AUTO_INCREMENT NOT NULL, usergroup_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, flag TINYINT(1) DEFAULT NULL, data LONGTEXT DEFAULT NULL, property_type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3F09DB16D2112630 (usergroup_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE user_masks (person_id INT NOT NULL, overrides LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_DF6A6B3C217BBB47 (person_id), PRIMARY KEY(person_id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE usersources (id INT AUTO_INCREMENT NOT NULL, person_scraper_id INT DEFAULT NULL, note LONGTEXT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, handler_class VARCHAR(255) NOT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', display_order INT NOT NULL, is_enabled TINYINT(1) NOT NULL, INDEX IDX_4E3C994C6CD3D8EC (person_scraper_id), PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE widgets (id INT AUTO_INCREMENT NOT NULL, name_id VARCHAR(200) NOT NULL, note VARCHAR(255) NOT NULL, assets_css LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', assets_js LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', section VARCHAR(200) NOT NULL, js_widget_class VARCHAR(200) DEFAULT NULL, php_widget_class VARCHAR(200) DEFAULT NULL, template_name VARCHAR(200) DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "CREATE TABLE worker_jobs (id VARCHAR(50) NOT NULL, worker_group VARCHAR(50) DEFAULT NULL, title VARCHAR(100) NOT NULL, description VARCHAR(100) NOT NULL, job_class VARCHAR(100) NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', run_interval INT NOT NULL, last_run_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB";

$queries[] = "ALTER TABLE sessions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE sessions ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE people ADD FOREIGN KEY (picture_blob_id) REFERENCES blobs(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE people ADD FOREIGN KEY (locale_id) REFERENCES locales(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE people ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE people ADD FOREIGN KEY (primary_email_id) REFERENCES people_emails(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE person2usergroups ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE person2usergroups ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE visitors ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE blobs ADD FOREIGN KEY (original_blob_id) REFERENCES blobs(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE locales ADD FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE people_emails ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE labels_people ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_data_person ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_data_person ADD FOREIGN KEY (field_id) REFERENCES custom_def_ticket(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE people_contact_data ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE people_prefs ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE person_usersource_assoc ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE person_usersource_assoc ADD FOREIGN KEY (usersource_id) REFERENCES usersources(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE person_scraper_assoc ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE person_scraper_assoc ADD FOREIGN KEY (person_scraper_id) REFERENCES usersources(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE twitter_accounts ADD FOREIGN KEY (user_id) REFERENCES twitter_users(id)";

$queries[] = "ALTER TABLE twitter_accounts_person ADD FOREIGN KEY (account_id) REFERENCES twitter_accounts(id)";

$queries[] = "ALTER TABLE twitter_accounts_person ADD FOREIGN KEY (person_id) REFERENCES people(id)";

$queries[] = "ALTER TABLE twitter_statuses_notes ADD FOREIGN KEY (status_id) REFERENCES twitter_statuses(id)";

$queries[] = "ALTER TABLE twitter_statuses_notes ADD FOREIGN KEY (person_id) REFERENCES people(id)";

$queries[] = "ALTER TABLE tasks ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tasks ADD FOREIGN KEY (assigned_agent_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tasks ADD FOREIGN KEY (assigned_agent_team_id) REFERENCES agent_teams(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE task_comments ADD FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE task_comments ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE task_associations ADD FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE task_associations ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE task_associations ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE task_associations ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE languages ADD FOREIGN KEY (parent_id) REFERENCES languages(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE agent_access ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE agent_department_members ADD FOREIGN KEY (person_id) REFERENCES agent_access(person_id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE agent_department_members ADD FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE agent_notifications ADD FOREIGN KEY (filter_id) REFERENCES ticket_filters(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE agent_notifications ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE agent_team_members ADD FOREIGN KEY (team_id) REFERENCES agent_teams(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE agent_team_members ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE api_auth_codes ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE api_auth_tokens ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE api_keys ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE articles ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE articles ADD FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_to_product ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_to_product ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_to_categories ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_to_categories ADD FOREIGN KEY (category_id) REFERENCES article_categories(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_categories ADD FOREIGN KEY (parent_id) REFERENCES article_categories(id)";

$queries[] = "ALTER TABLE article_category_permissions ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_category_permissions ADD FOREIGN KEY (category_id) REFERENCES article_categories(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_comments ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_comments ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE article_comments ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE article_pending_create ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_pending_create ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_ratings ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_ratings ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE article_ratings ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE article_revisions ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_revisions ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE article_validating_edits ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE article_validating_edits ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE blob_object_attach ADD FOREIGN KEY (blob_id) REFERENCES blobs(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE chat_conversations ADD FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE chat_conversations ADD FOREIGN KEY (agent_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE chat_conversations ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE chat_conversations ADD FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE chat_conversations ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE chat_conversation_to_person ADD FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE chat_conversation_to_person ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE chat_messages ADD FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE chat_messages ADD FOREIGN KEY (author_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE chat_quick_replies ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE client_channel_subscriptions ADD FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE client_messages ADD FOREIGN KEY (for_person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE content_search ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE content_search ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE content_search_attribute ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE content_search_attribute ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE custom_data_organizations ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_data_organizations ADD FOREIGN KEY (field_id) REFERENCES custom_def_organizations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_data_ticket ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_data_ticket ADD FOREIGN KEY (field_id) REFERENCES custom_def_ticket(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_def_organizations ADD FOREIGN KEY (parent_id) REFERENCES custom_def_organizations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_def_organizations ADD FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE custom_def_people ADD FOREIGN KEY (parent_id) REFERENCES custom_def_people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_def_people ADD FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE custom_def_ticket ADD FOREIGN KEY (parent_id) REFERENCES custom_def_ticket(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE custom_def_ticket ADD FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE departments ADD FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE downloads ADD FOREIGN KEY (category_id) REFERENCES download_categories(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE downloads ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE downloads ADD FOREIGN KEY (blob_id) REFERENCES blobs(id)";

$queries[] = "ALTER TABLE download_categories ADD FOREIGN KEY (parent_id) REFERENCES download_categories(id)";

$queries[] = "ALTER TABLE download_category_permissions ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE download_category_permissions ADD FOREIGN KEY (category_id) REFERENCES download_categories(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE email_gateway_logs ADD FOREIGN KEY (gateway_id) REFERENCES email_gateways(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE email_gateway_logs ADD FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE email_sources ADD FOREIGN KEY (gateway_id) REFERENCES email_gateways(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE email_sources_blobs ADD FOREIGN KEY (source_id) REFERENCES email_sources(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE error_logs ADD FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE ideas ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE ideas ADD FOREIGN KEY (status_category_id) REFERENCES idea_status_categories(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE ideas ADD FOREIGN KEY (category_id) REFERENCES idea_categories(id)";

$queries[] = "ALTER TABLE ideas ADD FOREIGN KEY (first_comment_id) REFERENCES idea_comments(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE idea_categories ADD FOREIGN KEY (parent_id) REFERENCES idea_categories(id)";

$queries[] = "ALTER TABLE idea_category_permissions ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE idea_category_permissions ADD FOREIGN KEY (category_id) REFERENCES idea_categories(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE idea_comments ADD FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE idea_comments ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE idea_comments ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE idea_votes ADD FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE idea_votes ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE idea_votes ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE labels_articles ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE labels_blobs ADD FOREIGN KEY (blob_id) REFERENCES blobs(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE labels_downloads ADD FOREIGN KEY (download_id) REFERENCES downloads(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE labels_ideas ADD FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE labels_news ADD FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE labels_organizations ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE labels_tasks ADD FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE labels_tickets ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE news ADD FOREIGN KEY (category_id) REFERENCES download_categories(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE news ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE news_categories ADD FOREIGN KEY (parent_id) REFERENCES download_categories(id)";

$queries[] = "ALTER TABLE news_category_permissions ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE news_category_permissions ADD FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE news_comments ADD FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE news_comments ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE news_comments ADD FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE organizations_contact_data ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE organization_notes ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE organization_notes ADD FOREIGN KEY (agent_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE person_logs ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE people_notes ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE people_notes ADD FOREIGN KEY (agent_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE person_scraper ADD FOREIGN KEY (usersource_id) REFERENCES usersources(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE person_stream ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE phrases ADD FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE plugin_listeners ADD FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE products ADD FOREIGN KEY (parent_id) REFERENCES products(id)";

$queries[] = "ALTER TABLE ratings_article ADD FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE ratings_article ADD FOREIGN KEY (person_id) REFERENCES people(id)";

$queries[] = "ALTER TABLE ratings_idea ADD FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE ratings_idea ADD FOREIGN KEY (person_id) REFERENCES people(id)";

$queries[] = "ALTER TABLE result_cache ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE sendmail_queue_part ADD FOREIGN KEY (sendmail_queue_id) REFERENCES sendmail_queue(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE styles ADD FOREIGN KEY (parent_id) REFERENCES styles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE style_resources ADD FOREIGN KEY (style_id) REFERENCES styles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE templates ADD FOREIGN KEY (style_id) REFERENCES styles(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (category_id) REFERENCES ticket_categories(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (priority_id) REFERENCES ticket_priorities(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (workflow_id) REFERENCES ticket_workflows(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (person_email_id) REFERENCES people_emails(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (agent_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (agent_team_id) REFERENCES agent_teams(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (email_gateway_id) REFERENCES email_gateways(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets ADD FOREIGN KEY (locked_by_agent) REFERENCES people(id)";

$queries[] = "ALTER TABLE ticket_access_codes ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE ticket_access_codes ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_attachments ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_attachments ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE tickets_attachments ADD FOREIGN KEY (blob_id) REFERENCES blobs(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_attachments ADD FOREIGN KEY (message_id) REFERENCES tickets_messages(id)";

$queries[] = "ALTER TABLE ticket_categories ADD FOREIGN KEY (parent_id) REFERENCES ticket_categories(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_deleted ADD FOREIGN KEY (by_person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE ticket_feedback ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE ticket_feedback ADD FOREIGN KEY (message_id) REFERENCES tickets_messages(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE ticket_feedback ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE ticket_filters ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE ticket_filters ADD FOREIGN KEY (agent_team_id) REFERENCES agent_teams(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE ticket_filters_perms ADD FOREIGN KEY (filter_id) REFERENCES ticket_filters(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_logs ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_logs ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE ticket_macros ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE ticket_macros_perms ADD FOREIGN KEY (macro_id) REFERENCES ticket_macros(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_messages ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_messages ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE ticket_page_display ADD FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_participants ADD FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_participants ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE tickets_participants ADD FOREIGN KEY (access_code_id) REFERENCES ticket_access_codes(id)";

$queries[] = "ALTER TABLE tickets_participants ADD FOREIGN KEY (person_email_id) REFERENCES people_emails(id) ON DELETE SET NULL";

$queries[] = "ALTER TABLE twitter_accounts_followers ADD FOREIGN KEY (account_id) REFERENCES twitter_accounts(id)";

$queries[] = "ALTER TABLE twitter_accounts_followers ADD FOREIGN KEY (user_id) REFERENCES twitter_users(id)";

$queries[] = "ALTER TABLE twitter_accounts_friends ADD FOREIGN KEY (account_id) REFERENCES twitter_accounts(id)";

$queries[] = "ALTER TABLE twitter_accounts_friends ADD FOREIGN KEY (user_id) REFERENCES twitter_users(id)";

$queries[] = "ALTER TABLE twitter_accounts_searches ADD FOREIGN KEY (account_id) REFERENCES twitter_accounts(id)";

$queries[] = "ALTER TABLE twitter_statuses ADD FOREIGN KEY (user_id) REFERENCES twitter_users(id)";

$queries[] = "ALTER TABLE twitter_statuses ADD FOREIGN KEY (in_reply_to_status_id) REFERENCES twitter_statuses(id)";

$queries[] = "ALTER TABLE twitter_statuses ADD FOREIGN KEY (retweet_id) REFERENCES twitter_statuses(id)";

$queries[] = "ALTER TABLE twitter_statuses ADD FOREIGN KEY (in_reply_to_user_id) REFERENCES twitter_users(id)";

$queries[] = "ALTER TABLE twitter_statuses ADD FOREIGN KEY (recipient_id) REFERENCES twitter_users(id)";

$queries[] = "ALTER TABLE twitter_statuses_long ADD FOREIGN KEY (status_id) REFERENCES twitter_statuses(id)";

$queries[] = "ALTER TABLE twitter_statuses_mentions ADD FOREIGN KEY (status_id) REFERENCES twitter_statuses(id)";

$queries[] = "ALTER TABLE twitter_statuses_mentions ADD FOREIGN KEY (user_id) REFERENCES twitter_users(id)";

$queries[] = "ALTER TABLE twitter_statuses_tags ADD FOREIGN KEY (status_id) REFERENCES twitter_statuses(id)";

$queries[] = "ALTER TABLE twitter_statuses_urls ADD FOREIGN KEY (status_id) REFERENCES twitter_statuses(id)";

$queries[] = "ALTER TABLE usergroup_properties ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE user_masks ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE";

$queries[] = "ALTER TABLE usersources ADD FOREIGN KEY (person_scraper_id) REFERENCES person_scraper(id) ON DELETE CASCADE";