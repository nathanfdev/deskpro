@new
Feature: /ticket_forms
  I want to check department field

  Background:
    Given I'm authenticated as admin
    And only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 |        | Department 1 | 1                  |
      | d2 |        | Department 2 | 1                  |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario Outline: I change department
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "department": <ref>
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.department" should be equal to "<ref>"

    Examples:
      | ref  |
      | ~d1~ |
      | ~d2~ |

  Scenario: I set department fields
    Given only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
      | f2 | text | Text field |
    And no TicketLayout records exist
    And the ticket layout exists for "d1" department with fields:
      | agent_layout      |
      | ticket_field_{f1} |
    And the ticket layout exists for "d2" department with fields:
      | agent_layout      |
      | ticket_field_{f2} |

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.fields" should have 0 elements

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "department": ~d1~,
  "fields": {
    "~f1~": "text 1"
  }
}
    """

    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.fields" should have 1 element
    And the JSON node "data.fields.{f1}.value" should be equal to "text 1"

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "department": ~d2~,
  "fields": {
    "~f2~": "text 2"
  }
}
    """

    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.fields" should have 2 elements
    And the JSON node "data.fields.{f1}.value" should be equal to "text 1"
    And the JSON node "data.fields.{f2}.value" should be equal to "text 2"
