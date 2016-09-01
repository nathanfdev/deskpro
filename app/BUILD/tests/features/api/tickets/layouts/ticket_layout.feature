@new
Feature: /ticket_layouts endpoint
  To DeskPRO ticket layouts
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And the setting "core.use_product" is set to 1
    And the setting "core.use_ticket_priority" is set to 1
    And the setting "core.use_ticket_category" is set to 1
    And the setting "core.use_ticket_workflow" is set to 1

  Scenario: I want to see ticket layouts w/o context
    Given the only default ticket layout exists with fields:
      | user_layout | agent_layout |
      | cc          | cc           |
    When I send a GET request to "/api/v2/ticket_layouts"
    Then the response status code should be 404

  Scenario: I want to see ticket layouts with unknown context
    Given the only default ticket layout exists with fields:
      | user_layout | agent_layout |
      | cc          | cc           |
    When I send a GET request to "/api/v2/ticket_layouts/unknown_context"
    Then the response status code should be 404

  Scenario Outline: I want to see ticket layout with unknown context or wrong department id
    Given the only default ticket layout exists with fields:
      | user_layout | agent_layout |
      | cc          | cc           |
    When I send a GET request to "/api/v2/ticket_layouts/<context>/<department_id>"
    Then the response status code should be 404

    Examples:
      | context         | department_id |
      | unknown_context | not_int       |
      | unknown_context | {d1}          |
      | agent           | 0             |
      | agent           | -1            |
      | user            | -1            |

  Scenario Outline: I check ticket layout base props
    Given the only default ticket layout exists with fields:
      | <context>_layout |
      | cc               |

    When I send a GET request to "/api/v2/ticket_layouts/<context>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "[0].department" should be null
    And the JSON node "[0].context" should be equal to <context>
    And the JSON node "[1].fields" should not exist

    Examples:
      | context |
      | user    |
      | agent   |

  Scenario: I check agent layout field order
    Given the only default ticket layout exists with fields:
      | agent_layout |
      | cc           |

    When I send a GET request to "/api/v2/ticket_layouts/agent"
    Then the JSON node "[0].fields" should have 6 elements
    And the JSON node "[0].fields[0].field_id" should be equal to "person"
    And the JSON node "[0].fields[1].field_id" should be equal to "department"
    And the JSON node "[0].fields[2].field_id" should be equal to "cc"
    And the JSON node "[0].fields[3].field_id" should be equal to "labels"
    And the JSON node "[0].fields[4].field_id" should be equal to "subject"
    And the JSON node "[0].fields[5].field_id" should be equal to "message"

  Scenario: I check user layout field order
    Given the only default ticket layout exists with fields:
      | user_layout |
      | cc          |

    When I send a GET request to "/api/v2/ticket_layouts/user"
    Then the JSON node "[0].fields" should have 6 elements
    And the JSON node "[0].fields[0].field_id" should be equal to "cc"
    And the JSON node "[0].fields[1].field_id" should be equal to "department"
    And the JSON node "[0].fields[2].field_id" should be equal to "subject"
    And the JSON node "[0].fields[3].field_id" should be equal to "message"
    And the JSON node "[0].fields[4].field_id" should be equal to "person"
    And the JSON node "[0].fields[5].field_id" should be equal to "labels"

  Scenario Outline: I check custom department layout
    Given the ticket layout exists for "<department>" department with fields:
      | <context>_layout |
      | cc               |
    When I send a GET request to "/api/v2/ticket_layouts/<context>/<department>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "fields[0].field_id" should be equal to "<expected_field>"

    Examples:
      | context | department | expected_field |
      | agent   | {d1}       | person         |
      | agent   | {d2}       | person         |
      | user    | {d1}       | cc             |
      | user    | {d2}       | cc             |

    Scenario Outline: I check default department layout
      Given the only default ticket layout exists with fields:
        | <context>_layout |
        | cc               |
      When I send a GET request to "/api/v2/ticket_layouts/<context>/default"
      Then the response status code should be 200
      And the response should be in JSON
      And the JSON node "fields[0].field_id" should be equal to "<expected_field>"

      Examples:
        | context | expected_field |
        | agent   | person         |
        | agent   | person         |
        | user    | cc             |
        | user    | cc             |

  Scenario Outline: I want to see ticket layout with disabled product, priority, category and workflow settings
    Given the setting "core.use_product" is set to <enabled>
    And the setting "core.use_ticket_priority" is set to <enabled>
    And the setting "core.use_ticket_category" is set to <enabled>
    And the setting "core.use_ticket_workflow" is set to <enabled>
    And the only default ticket layout exists with fields:
      | <context>_layout |
      | priority         |
      | category         |
      | workflow         |
      | product          |

    When I send a GET request to "/api/v2/ticket_layouts/<context>/default"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "fields" should have <expected_count> elements

    Examples:
      | context | enabled | expected_count |
      | agent   | 0       | 5              |
      | agent   | 1       | 9              |
      | user    | 0       | 5              |
      | user    | 1       | 9              |

  Scenario Outline: I want to see all ticket layouts
    Given the only default ticket layout exists with fields:
      | <context>_layout |
      | cc               |
    And the ticket layout exists for "{d1}" department with fields:
      | <context>_layout |
      | cc               |
    And the ticket layout exists for "{d2}" department with fields:
      | <context>_layout |
      | cc               |

    When I send a GET request to "/api/v2/ticket_layouts/<context>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "[0].fields" should exist
    And the JSON node "[1].fields" should exist
    And the JSON node "[2].fields" should exist
    And the JSON node "[3].fields" should not exist

    Examples:
      | context |
      | agent   |
      | user    |
