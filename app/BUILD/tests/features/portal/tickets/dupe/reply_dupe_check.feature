@new
Feature: Ticket reply dupe check

  Background:
    Given I'm authenticated as user
    And I have only default brand
    And user has defaultBrand brand
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for registered usergroup
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered

  Scenario: I try to create dupe reply
    Given only the following Ticket records exist:
      | #        | Person | Department | Brand          | Ref | Subject        | Date Last User Reply | Message              |
      | ticket_1 | {user} | {d1}       | {defaultBrand} | ref | Ticket Subject | 2015-06-15 17:00:00  | Ticket message text. |

    When I go to "/tickets/ref"
    And I fill in "ticket_reply[ticket_message][message]" with "Ticket message text."
    And I press "Reply"
    Then the response should contain "Duplicate ticket message."
