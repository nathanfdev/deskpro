@new
Feature: /ticket_forms
  I want to check message field

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I add ticket description w/o format
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "message": {
    "message": "<p>my html message</p>"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should contain "<p>my html message"

  Scenario: I add ticket description in html format
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "message": {
    "message": "<p>my html message</p>",
    "format": "html"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should contain "<p>my html message"

  Scenario: I add ticket description in text format
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "message": {
    "message": "<p>my html message</p>",
    "format": "text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should contain "&lt;p&gt;my html message"
