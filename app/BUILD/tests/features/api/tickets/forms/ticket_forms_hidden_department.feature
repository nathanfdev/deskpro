@new
Feature: /ticket_forms endpoint
  I want to check skipped department field if there is just one selectable department

  Background:
    Given I'm authenticated as admin
    And I have default brand
    And there are no Department records
    And the only default ticket layout exists with fields:
      | agent_layout |
      | department   |

  Scenario: I have just one department
    Given only the following Department records exist:
      | # | Title      | Brand          | Is Tickets Enabled |
      | d | Department | {defaultBrand} | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "department": ~d~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to the string "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to the string "Unexpected field names: department"

  Scenario: I have just one selectable department
    Given only the following Department records exist:
      | #  | Parent | Title          | Brand          | Is Tickets Enabled |
      | d1 |        | Department 1   | {defaultBrand} | 1                  |
      | d2 | {d1}   | Department 1a  | {defaultBrand} | 1                  |
      | d3 | {d2}   | Department 1aa | {defaultBrand} | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "department": ~d3~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to the string "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to the string "Unexpected field names: department"

  Scenario: I have more than one selectable department
    Given only the following Department records exist:
      | #  | Parent | Title          | Brand          | Is Tickets Enabled |
      | d1 |        | Department 1   | {defaultBrand} | 1                  |
      | d2 | {d1}   | Department 1a  | {defaultBrand} | 1                  |
      | d3 | {d1}   | Department 1b  | {defaultBrand} | 1                  |
      | d4 | {d2}   | Department 1aa | {defaultBrand} | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "subject": "Sample Ticket",
    "department": ~d4~,
    "message": {
      "message": "<p>my html message</p>",
      "format": "html"
    }
}
    """
    Then the response status code should be 201
    And the JSON node "data.department" should be equal to "{d4}"
