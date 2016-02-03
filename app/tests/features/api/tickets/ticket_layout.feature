Feature: /ticket_layouts endpoint
  To DeskPRO ticket layouts
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I want to see ticket layouts without context or with unknown context
    When I send a GET request to "/api/v2/ticket_layouts"
    Then the response status code should be 404

    When I send a GET request to "/api/v2/ticket_layouts/unknown_context"
    Then the response status code should be 404

  Scenario Outline: I want to see all ticket layouts
    When I send a GET request to "/api/v2/ticket_layouts/<context>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "[0].department" should be equal to 0
    And the JSON node "[0].context" should be equal to <context>
    And the JSON node "[0].fields" should exist

    Examples:
      | context |
      | user    |
      | agent   |

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
    And the JSON node "fields" should exist

    Examples:
      | context | department_id | expected_department_id |
      | agent   |  1            | 1                      |
      | agent   |  2            | 2                      |
      | agent   |  default      | 0                      |
      | user    |  1            | 1                      |
      | user    |  2            | 2                      |
      | user    |  default      | 0                      |
