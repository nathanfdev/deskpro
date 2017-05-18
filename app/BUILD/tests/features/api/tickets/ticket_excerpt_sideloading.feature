@new
Feature: /tickets endpoint
  To check excerpt sideloading
  Agent notes should be included only for agents

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I check excerpt
    Given only the following TicketMessage records exist:
      | #  | Ticket | Message | Is Agent Note |
      | m1 | {t1}   | Message | 0             |
      | m2 | {t1}   | Note    | 1             |

    When I send a GET request to "/api/v2/tickets/{t1}?include=ticket_excerpt"
    Then the JSON node "linked.ticket_excerpt.{t1}.message_id" should be equal to "{m2}"
    And the JSON node "linked.ticket_excerpt.{t1}.excerpt" should be equal to "Note"
