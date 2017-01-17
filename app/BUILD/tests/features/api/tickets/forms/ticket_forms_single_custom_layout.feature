@new
Feature: /ticket_forms endpoint
  Only 1 department w/ custom layout

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |

  Scenario: I make sure the correct custom layout is applied when the department is set implicitly
    Given only the following custom ticket fields exist:
      | #  | Type     | Title          |
      | f1 | text     | Text field     |
      | f2 | textarea | Textarea field |
    And the ticket layout exists for "{d1}" department with fields:
      | agent_layout      |
      | ticket_field_{f1} |
      | ticket_field_{f2} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "My ticket",
  "message": {
    "message": "My message"
  },
  "fields": {
    "~f1~": "val 1",
    "~f2~": "val 2"
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.fields.{f1}.value" should be equal to the string "val 1"
    And the JSON node "data.fields.{f2}.value" should be equal to the string "val 2"
