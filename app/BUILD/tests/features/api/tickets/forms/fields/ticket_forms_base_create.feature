@new
Feature: /ticket_forms
  I want to check subject field

  Background:
    Given I'm authenticated as admin
    And I have default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |

  Scenario: I check ticket creation
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "Sample Ticket",
  "department": ~d1~,
  "message": {
    "message": "<p>my html message</p>",
    "format": "html"
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.subject" should be equal to the string "Sample Ticket"
    And the JSON node "data.department" should be equal to "{d1}"

    When I send a GET request to "/api/v2/tickets/{lastCreatedId}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should contain "<p>my html message"
