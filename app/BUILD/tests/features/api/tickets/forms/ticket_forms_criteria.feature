@new
Feature: /ticket_forms endpoint
  I want to check fields criteria

  Background:
    Given I'm authenticated as admin
    And I have default brand
    And a user with "user_1@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |

  Scenario: I check field is present on the form on department criteria
    Given the only default ticket layout exists with fields:
      | agent_layout | agent_layout_options                                                                                                                               |
      | cc           | {"on_newticket": true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckDepartment","op":"is","options":{"department_ids":["~d2~"]}}]}} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "cc": ["user_1@deskpro.dev"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: cc"

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~user_1@deskpro.dev~,
  "message": {
    "message": "my message"
  },
  "department": ~d2~,
  "cc": ["user_1@deskpro.dev"]
}
    """
    Then the response status code should be 201
