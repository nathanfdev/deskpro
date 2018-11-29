@new
Feature: /20170601/ticket_statuses endpoint
  To CRUD DeskPRO ticket's statuses
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
      | t2 | Ticket 2 | awaiting_agent |
      | t3 | Ticket 3 | awaiting_agent |
      | t4 | Ticket 4 | awaiting_user |

  Scenario: I retrieve a list of ticket statuses
    When I send a GET request to "/api/v2/20170601/ticket_statuses"
    Then the response status code should be 200
    And the JSON node "data[0]" should be equal to the string "awaiting_agent"
    And the JSON node "data[1]" should be equal to the string "awaiting_user"
    And the JSON node "data[2]" should be equal to the string "archived"
    And the JSON node "data[3]" should be equal to the string "resolved"
    And the JSON node "data[4]" should be equal to the string "hidden"
    And the JSON node "data[5]" should be equal to the string "hidden.spam"
    And the JSON node "data[6]" should be equal to the string "hidden.deleted"

  Scenario Outline: I retrieve status' tickets
    When I send a GET request to "/api/v2/20170601/ticket_statuses/<status>/tickets"
    Then the response status code should be 200
    And the JSON node "data" should have <count> elements

    Examples:
      | status         | count |
      | awaiting_agent | 3     |
      | awaiting_user  | 1     |
      | archived       | 0     |
      | resolved       | 0     |
      | hidden         | 0     |
      | hidden.spam    | 0     |
      | hidden.deleted | 0     |
