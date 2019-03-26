@new
Feature: Discover settings

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | single_choice | Text field |
      | f2 | single_choice | Text field |
    And only the following Language records exist:
      | #  | Sys Name |
      | l1 | default  |
      | l2 | lang_2   |

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
    And the setting "beta_features.new_snippets" is set to 1
    When I send a GET request to "/api/v2/helpdesk/agent-client/info"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.settings.multi_lang" should be equal to 1
    And the JSON node "data.settings.helpdesk_name" should be equal to "DeskPRO"
    And the JSON node "data.settings.attachments.agents.max_size" should be equal to 26214400
    And the JSON node "data.settings.attachments.agents.whitelist" should have 0 elements
    And the JSON node "data.settings.attachments.agents.blacklist" should have 0 elements
    And the JSON node "data.settings.features" should have 1 elements
    And the JSON node "data.settings.features[0]" should be equal to "new_snippets"

    And the JSON node "data.account_info.timezone" should be equal to "UTC"
    And the JSON node "data.account_info.language" should be equal to "{l1}"
    And the JSON node "data.account_info.signature_html" should exist

    And the JSON node "data.tickets.enabled" should be equal to 1
    And the JSON node "data.tickets.ref_code" should be equal to 0
    And the JSON node "data.tickets.archiving" should be equal to 1

    And the JSON node "data.tickets.field_info.product.enabled" should exist
    And the JSON node "data.tickets.field_info.product.default_id" should be equal to 0

    And the JSON node "data.tickets.field_info.category.enabled" should exist
    And the JSON node "data.tickets.field_info.category.default_id" should be equal to 0

    And the JSON node "data.tickets.field_info.workflow.enabled" should exist
    And the JSON node "data.tickets.field_info.workflow.default_id" should be equal to 0

    And the JSON node "data.tickets.field_info.priority.enabled" should exist
    And the JSON node "data.tickets.field_info.priority.default_id" should be equal to 0

    And the JSON node "data.tickets.field_info.custom.has_any" should be equal to 1

    And the JSON node "data.tickets.billing.enabled" should be equal to 0
    And the JSON node "data.tickets.billing.currency_name" should be equal to 0

    And the JSON node "data.tickets.timelog.enabled" should be equal to 0
    
    And the JSON node "data.tickets.group_fields" should be equal to node:
      """
      [
        {
          "id": "status",
          "type": "status",
          "field_id": null
        },
        {
          "id": "department",
          "type": "department",
          "field_id": null
        },
        {
          "id": "agent",
          "type": "agent",
          "field_id": null
        },
        {
          "id": "agent_team",
          "type": "agent_team",
          "field_id": null
        },
        {
          "id": "urgency",
          "type": "urgency",
          "field_id": null
        },
        {
          "id": "date_created",
          "type": "date_created",
          "field_id": null
        },
        {
          "id": "language",
          "type": "language",
          "field_id": null
        },
        {
          "id": "ticket_field.~f1~",
          "type": "ticket_field",
          "field_id": ~f1~
        },
        {
          "id": "ticket_field.~f2~",
          "type": "ticket_field",
          "field_id": ~f2~
        }
      ]
      """

    And the JSON node "data.tickets.order_fields" should be equal to node:
      """
      [
        {
          "id": "urgency",
          "type": "urgency"
        },
        {
          "id": "date_created",
          "type": "date_created"
        },
        {
          "id": "date_last_agent_reply",
          "type": "date_last_agent_reply"
        },
        {
          "id": "date_last_user_reply",
          "type": "date_last_user_reply"
        },
        {
          "id": "date_last_reply",
          "type": "date_last_reply"
        },
        {
          "id": "date_user_waiting",
          "type": "date_user_waiting"
        },
        {
          "id": "total_user_waiting",
          "type": "total_user_waiting"
        }
      ]
      """

    And the JSON node "data.chat.enabled" should exist
    And the JSON node "data.crm.enabled" should exist
    And the JSON node "data.feedback.enabled" should exist
    And the JSON node "data.publish.enabled" should exist
    And the JSON node "data.tasks.enabled" should exist
