@new
Feature: New ticket dupe check

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |


  Scenario: I try to create dupe ticket
    Given only the following Ticket records exist:
      | #        | Person  | Agent | Department | Brand          | Ref | Subject        | Date Last User Reply | Message              |
      | ticket_1 | {admin} | NULL  | {d1}       | {defaultBrand} | ref | Ticket Subject | 2015-06-15 17:00:00  | Ticket message text. |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "Ticket Subject",
  "department": ~d1~,
  "message": {
    "message": "Ticket message text."
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "dupe_ticket"
