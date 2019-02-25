@new
Feature: Resolve ticket

  Background:
    Given I have only default brand
    And agent and user exist
    And I'm authenticated as user
    And user has defaultBrand brand
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for registered usergroup
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And only the following Ticket records exist:
      | #  | Person | Department | Ref | Auth | Subject        | Date Last User Reply |
      | t1 | {user} | {d1}       | ref | AAA  | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | #  | Ticket | Person  | Message |
      | m1 | {t1}   | {user}  | text    |
      | m2 | {t1}   | {agent} | text    |
    And the only default ticket layout exists with fields:
      | user_layout |
      | cc          |

  Scenario: I resolve ticket if satisfaction feedback is disabled
    Given the setting "core_tickets.enable_feedback" is set to 0
    When I go to "/tickets/ref/resolve"
    And press "Resolve my ticket"
    Then I should be on "/tickets/ref"

  Scenario: I resolve ticket if satisfaction feedback is enabled
    Given the setting "core_tickets.enable_feedback" is set to 1
    When I go to "/tickets/ref/resolve"
    And press "Resolve my ticket"
    Then I should be on "/ticket-rate/ref/AAA"
