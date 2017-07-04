@new
Feature: Custom fields
  I check that I can set date field less than 1970 year

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
    And only the following custom ticket fields exist:
      | #  | Type     | Title          |
      | f1 | date     | Date field     |
      | f2 | datetime | Datetime field |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |
      | ticket_field_{f2} |

  Scenario: I check date fields
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": "1950-07-03",
    "~f2~": "1951-07-03 19:00:00"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should be equal to "1950-07-03T00:00:00+0000"
    And the JSON node "data.fields.{f2}.value" should be equal to "1951-07-03T19:00:00+0000"
