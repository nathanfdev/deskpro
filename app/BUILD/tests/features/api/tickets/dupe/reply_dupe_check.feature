@new
Feature: Ticket reply dupe check

  Background:
    Given no Ticket records exist
    And I'm authenticated as admin
    And I have default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |

  Scenario: I try to add dupe message
    Given only the following Ticket records exist:
      | # | Person  | Agent | Department | Brand          | Ref | Subject        | Date Last User Reply | Message              |
      | t | {admin} | NULL  | {d1}       | {defaultBrand} | ref | Ticket Subject | 2015-06-15 17:00:00  | Ticket message text. |

    When I send a POST request to "/api/v2/tickets/{t}/messages" with body:
    """
{
  "message": "Ticket message text."
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "dupe_ticket_message"
