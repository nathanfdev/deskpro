Feature: Discover settings

  Background:
    Given I install the api data set
    And my request is authenticated
    And I have only default brand

  Scenario: I get discover settings
    When I send a GET request to "/api/v2/helpdesk/discover"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.is_deskpro" should be equal to 1
    And the JSON node "data.helpdesk_url" should exist
    And the JSON node "data.base_api_url" should exist
    And the JSON node "data.build" should exist

  Scenario: I get agent client info settings
    Given only setting for brand "{defaultBrand}" with name "core.apps_chat" and value "1" exists
    When I send a GET request to "/api/v2/helpdesk/agent-client/info"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.settings.multi_lang" should be equal to 1
    And the JSON node "data.settings.helpdesk_name" should be equal to "DeskPRO"
    And the JSON node "data.settings.attachments.agents.max_size" should be equal to 26214400
    And the JSON node "data.settings.attachments.agents.whitelist" should have 0 elements
    And the JSON node "data.settings.attachments.agents.blacklist" should have 0 elements

    And the JSON node "data.account_info.timezone" should be equal to "UTC"
    And the JSON node "data.account_info.language" should be equal to 1
    And the JSON node "data.account_info.signature_html" should exist

    And the JSON node "data.tickets.enabled" should be equal to 1
    And the JSON node "data.tickets.ref_code" should be equal to 0
    And the JSON node "data.tickets.archiving" should be equal to 1

    And the JSON node "data.tickets.field_info.product.enabled" should be equal to 0
    And the JSON node "data.tickets.field_info.product.default_id" should be equal to 0

    And the JSON node "data.tickets.field_info.category.enabled" should be equal to 0
    And the JSON node "data.tickets.field_info.category.default_id" should be equal to 0

    And the JSON node "data.tickets.field_info.workflow.enabled" should be equal to 0
    And the JSON node "data.tickets.field_info.workflow.default_id" should be equal to 0

    And the JSON node "data.tickets.field_info.priority.enabled" should be equal to 0
    And the JSON node "data.tickets.field_info.priority.default_id" should be equal to 0

    And the JSON node "data.tickets.field_info.custom.has_any" should be equal to 1

    And the JSON node "data.tickets.billing.enabled" should be equal to 0
    And the JSON node "data.tickets.billing.currency_name" should be equal to 0

    And the JSON node "data.tickets.timelog.enabled" should be equal to 0

    And the JSON node "data.tickets.group_fields[0].id" should be equal to "department"
    And the JSON node "data.tickets.group_fields[0].type" should be equal to "department"

    And the JSON node "data.tickets.group_fields[1].id" should be equal to "agent"
    And the JSON node "data.tickets.group_fields[1].type" should be equal to "agent"

    And the JSON node "data.tickets.group_fields[2].id" should be equal to "agent_team"
    And the JSON node "data.tickets.group_fields[2].type" should be equal to "agent_team"

    And the JSON node "data.tickets.group_fields[3].id" should be equal to "urgency"
    And the JSON node "data.tickets.group_fields[3].type" should be equal to "urgency"

    And the JSON node "data.tickets.group_fields[4].id" should be equal to "waiting_time"
    And the JSON node "data.tickets.group_fields[4].type" should be equal to "waiting_time"

    And the JSON node "data.tickets.group_fields[5].id" should be equal to "all_waiting_time"
    And the JSON node "data.tickets.group_fields[5].type" should be equal to "all_waiting_time"

    And the JSON node "data.tickets.group_fields[6].id" should be equal to "date_created"
    And the JSON node "data.tickets.group_fields[6].type" should be equal to "date_created"

    And the JSON node "data.tickets.group_fields[7].id" should be equal to "language"
    And the JSON node "data.tickets.group_fields[7].type" should be equal to "language"

    And the JSON node "data.tickets.group_fields[8].id" should be equal to "organization"
    And the JSON node "data.tickets.group_fields[8].type" should be equal to "organization"

    And the JSON node "data.tickets.group_fields[9].id" should be equal to "person"
    And the JSON node "data.tickets.group_fields[9].type" should be equal to "person"

    And the JSON node "data.tickets.group_fields[10].id" should be equal to "ticket_field.6"
    And the JSON node "data.tickets.group_fields[10].type" should be equal to "ticket_field"
    And the JSON node "data.tickets.group_fields[10].field_id" should be equal to 6

    And the JSON node "data.tickets.group_fields[11].id" should be equal to "ticket_field.7"
    And the JSON node "data.tickets.group_fields[11].type" should be equal to "ticket_field"
    And the JSON node "data.tickets.group_fields[11].field_id" should be equal to 7

    And the JSON node "data.tickets.group_fields[12].id" should be equal to "ticket_field.1"
    And the JSON node "data.tickets.group_fields[12].type" should be equal to "ticket_field"
    And the JSON node "data.tickets.group_fields[12].field_id" should be equal to 1

    And the JSON node "data.tickets.group_fields[13].id" should be equal to "ticket_field.8"
    And the JSON node "data.tickets.group_fields[13].type" should be equal to "ticket_field"
    And the JSON node "data.tickets.group_fields[13].field_id" should be equal to 8

    And the JSON node "data.tickets.group_fields[14].id" should be equal to "ticket_field.12"
    And the JSON node "data.tickets.group_fields[14].type" should be equal to "ticket_field"
    And the JSON node "data.tickets.group_fields[14].field_id" should be equal to 12

    And the JSON node "data.tickets.group_fields[15].id" should be equal to "ticket_field.5"
    And the JSON node "data.tickets.group_fields[15].type" should be equal to "ticket_field"
    And the JSON node "data.tickets.group_fields[15].field_id" should be equal to 5

    And the JSON node "data.tickets.order_fields[0].id" should be equal to "urgency"
    And the JSON node "data.tickets.order_fields[0].type" should be equal to "urgency"

    And the JSON node "data.tickets.order_fields[1].id" should be equal to "date_created"
    And the JSON node "data.tickets.order_fields[1].type" should be equal to "date_created"

    And the JSON node "data.tickets.order_fields[2].id" should be equal to "date_last_agent_reply"
    And the JSON node "data.tickets.order_fields[2].type" should be equal to "date_last_agent_reply"

    And the JSON node "data.tickets.order_fields[3].id" should be equal to "date_last_user_reply"
    And the JSON node "data.tickets.order_fields[3].type" should be equal to "date_last_user_reply"

    And the JSON node "data.tickets.order_fields[4].id" should be equal to "date_last_reply"
    And the JSON node "data.tickets.order_fields[4].type" should be equal to "date_last_reply"

    And the JSON node "data.tickets.order_fields[5].id" should be equal to "date_user_waiting"
    And the JSON node "data.tickets.order_fields[5].type" should be equal to "date_user_waiting"

    And the JSON node "data.tickets.order_fields[6].id" should be equal to "total_user_waiting"
    And the JSON node "data.tickets.order_fields[6].type" should be equal to "total_user_waiting"

    And the JSON node "data.chat.enabled" should be equal to 1
    And the JSON node "data.crm.enabled" should be equal to 1
    And the JSON node "data.feedback.enabled" should be equal to 0
    And the JSON node "data.publish.enabled" should be equal to 0
    And the JSON node "data.tasks.enabled" should be equal to 0
