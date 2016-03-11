@tickets
Feature: /ticket_layouts endpoint
  To DeskPRO ticket layouts
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And the setting "core.use_product" is set to 1
    And the setting "core.use_ticket_priority" is set to 1
    And the setting "core.use_ticket_category" is set to 1
    And the setting "core.use_ticket_workflow" is set to 1

  @reinstall
  Scenario: I want to see ticket layouts without context or with unknown context
    When I send a GET request to "/api/v2/ticket_layouts"
    Then the response status code should be 404

    When I send a GET request to "/api/v2/ticket_layouts/unknown_context"
    Then the response status code should be 404

  Scenario: I want to see all ticket layouts
    When I send a GET request to "/api/v2/ticket_layouts/agent"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "[0].department" should be equal to 0
    And the JSON node "[0].context" should be equal to agent
    And the JSON node "[0].fields" should have 2 elements
    And the JSON node "[0].fields[0].field_type" should be equal to "department"
    And the JSON node "[0].fields[0].options.on_newticket" should be equal to 1
    And the JSON node "[0].fields[0].options.on_viewticket" should be equal to 1
    And the JSON node "[0].fields[0].options.on_viewticket_mode" should be equal to "always"
    And the JSON node "[0].fields[0].options.on_editticket" should be equal to 1
    And the JSON node "[0].fields[1].field_type" should be equal to "message"

    And the JSON node "[1].department" should be equal to 2
    And the JSON node "[1].context" should be equal to agent
    And the JSON node "[1].fields" should have 29 elements
    And the JSON node "[1].fields[0].field_type" should be equal to "person"
    And the JSON node "[1].fields[1].field_type" should be equal to "department"
    And the JSON node "[1].fields[2].field_type" should be equal to "message"
    And the JSON node "[1].fields[3].field_type" should be equal to "attachments"

  Scenario Outline: I want to see ticket layout with unknown context or wrong department id
    When I send a GET request to "/api/v2/ticket_layouts/<context>/<department_id>"
    Then the response status code should be 404

    Examples:
      | context         | department_id |
      | unknown_context |  not_int      |
      | unknown_context |  1            |
      | agent           |  0            |
      | agent           |  1000         |
      | user            |  1000         |

  Scenario Outline: I want to see ticket layout
    When I send a GET request to "/api/v2/ticket_layouts/<context>/<department_id>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "department" should be equal to <expected_department_id>
    And the JSON node "context" should be equal to <context>
    And the JSON node "fields" should have <expected_fields_count> elements

    Examples:
      | context | department_id | expected_department_id | expected_fields_count |
      | agent   |  1            | 1                      | 2                     |
      | agent   |  2            | 2                      | 29                    |
      | agent   |  default      | 0                      | 2                     |
      | user    |  1            | 1                      | 0                     |
      | user    |  2            | 2                      | 0                     |
      | user    |  default      | 0                      | 0                     |

  Scenario: I want to see ticket layout with disabled product, priority, category and workflow settings
    Given the setting "core.use_product" is set to 0
    And the setting "core.use_ticket_priority" is set to 0
    And the setting "core.use_ticket_category" is set to 0
    And the setting "core.use_ticket_workflow" is set to 0
    When I send a GET request to "/api/v2/ticket_layouts/agent/2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "fields" should have 25 elements
