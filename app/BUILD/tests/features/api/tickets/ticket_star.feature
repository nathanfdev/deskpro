@new
Feature: /ticket_stars endpoint
  To CRUD DeskPRO ticket's stars
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And no TicketFlagged records exist
    And no PersonPref records exist

  Scenario: I get list of my ticket stars
    Given only the following PersonPref records exist:
      | Person  | Name                | Value Str         |
      | {admin} | agent.ui.flag.green | Green custom name |
      | {admin} | agent.ui.flag.pink  | Pink custom name  |
      | {admin} | agent.ui.flag.red   | Red custom name   |

    When I send a GET request to "/api/v2/ticket_stars"
    Then the response status code should be 200
    And the JSON node "data" should have 7 elements
    And the JSON node "data[0].name" should be equal to "Blue"
    And the JSON node "data[1].name" should be equal to "Green custom name"
    And the JSON node "data[3].name" should be equal to "Pink custom name"
    And the JSON node "data[5].name" should be equal to "Red custom name"

  Scenario: I get counts of my ticket flagged by stars
    Given only the following Ticket records exist:
      | #  | Subject  | Person  |
      | t1 | Ticket 1 | {admin} |
      | t2 | Ticket 2 | {admin} |
      | t3 | Ticket 3 | {admin} |
      | t4 | Ticket 4 | {admin} |
      | t5 | Ticket 5 | {admin} |
    And only the following TicketFlagged records exist:
      | Person  | Ticket | color  |
      | {admin} | {t1}   | blue   |
      | {agent} | {t2}   | blue   |
      | {admin} | {t3}   | green  |
      | {agent} | {t4}   | green  |

    When I send a GET request to "/api/v2/ticket_stars/counts"
    Then the response status code should be 200
    And the JSON node "data.nested" should have 7 element
    And the JSON node "data.nested[0].count" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Blue"
    And the JSON node "data.nested[1].count" should be equal to 1
    And the JSON node "data.nested[1].title" should be equal to "Green"
    And the JSON node "data.nested[2].count" should be equal to 0

  Scenario: I set custom name
    When I send a PUT request to "/api/v2/ticket_stars/1"
    Then the response status code should be 204

    When I send a PUT request to "/api/v2/ticket_stars/1" with body:
    """
{
  "name": "blue custom"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_stars"
    And the JSON node "data[0].name" should be equal to "Blue custom"

  Scenario: I remove custom name
    Given only the following PersonPref records exist:
      | Person  | Name               | Value Str        |
      | {admin} | agent.ui.flag.blue | Blue custom name |

    When I send a PUT request to "/api/v2/ticket_stars/1" with body:
    """
{
  "name": ""
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_stars"
    And the JSON node "data[0].name" should be equal to "Blue"
