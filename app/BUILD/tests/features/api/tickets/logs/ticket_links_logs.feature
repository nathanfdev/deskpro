@new
Feature: Ticket logs when manipulating ticket relations

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And only the following Ticket records exist:
      | #        | Subject      | Parent ticket | Person |
      | ticket_1 | Ticket One   |               | {user} |
      | ticket_2 | Ticket Two   |               | {user} |
      | ticket_3 | Ticket Three | {ticket_1}    | {user} |
      | ticket_4 | Ticket Four  | {ticket_1}    | {user} |

  Scenario: I link two tickets as child and check logs
    Given I reset the "{ticket_1}" ticket logs
    Given I reset the "{ticket_2}" ticket logs
    When I send a "POST" request to "/api/v2/tickets/{ticket_1}/links" with body:
    """
{
  "parent": false,
  "link_ticket": ~ticket_2~
}
    """
    Then the response status code should be 204
    And the "{ticket_1}" ticket should not have "parent_ticket" log
    And the "{ticket_2}" ticket should have "parent_ticket" log

  Scenario: I link two tickets as parent and check logs
    Given I reset the "{ticket_1}" ticket logs
    Given I reset the "{ticket_2}" ticket logs
    When I send a "POST" request to "/api/v2/tickets/{ticket_1}/links" with body:
    """
{
  "parent": true,
  "link_ticket": ~ticket_2~
}
    """
    Then the response status code should be 204
    And the "{ticket_1}" ticket should have "parent_ticket" log
    And the "{ticket_2}" ticket should not have "parent_ticket" log

  Scenario: I unlink parent ticket
    Given I reset the "{ticket_3}" ticket logs
    When I send a "DELETE" request to "/api/v2/tickets/{ticket_3}/links" with body:
    """
{
  "link_type": "parent"
}
    """
    Then the response status code should be 204
    And the "{ticket_3}" ticket should have "parent_ticket" log

  Scenario: I unlink a child ticket
    Given I reset the "{ticket_4}" ticket logs
    When I send a "DELETE" request to "/api/v2/tickets/{ticket_1}/links" with body:
    """
{
  "link_type": "child",
  "link_ticket": ~ticket_4~
}
    """
    Then the response status code should be 204
    And the "{ticket_4}" ticket should have "parent_ticket" log

  Scenario: I unlink sibling ticket
    Given I reset the "{ticket_4}" ticket logs
    When I send a "DELETE" request to "/api/v2/tickets/{ticket_3}/links" with body:
    """
{
  "link_type": "sibling",
  "link_ticket": ~ticket_4~
}
    """
    Then the response status code should be 204
    And the "{ticket_4}" ticket should have "parent_ticket" log
