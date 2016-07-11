@new
Feature: Custom fields
  I want to check that unable to submit display field

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I check display field
    Given only the following custom ticket fields exist:
      | #  | Type    | Title         |
      | f1 | display | Display field |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": "some value"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should contain "fields"
