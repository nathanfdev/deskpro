@new
Feature: Add and edit ticket participants

  Background:
    Given no Person records exist
    And I have only default brand
    And I'm authenticated as user
    And user has defaultBrand brand
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for registered usergroup
    And a user with "user_1@deskpro.dev" email exists
    And a user with "user_2@deskpro.dev" email exists
    And an agent with "agent@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | {d1}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |
    And the only default ticket layout exists with fields:
      | user_layout |
      | cc          |

  Scenario: I edit ticket with cc
    Given I go to "/tickets/ref/edit"
    And I fill in "Cc" with "user_1@deskpro.dev,user_2@deskpro.dev"
    And I press "Save"
    When I go to "/tickets/ref/edit"
    Then the "Cc" field should contain "user_1@deskpro.dev,user_2@deskpro.dev"

  Scenario: I create new ticket with cc
    Given I go to "/new-ticket"
    And I fill in "Cc" with "user_1@deskpro.dev,user_2@deskpro.dev"
    When I press "Submit"
    Then the "Cc" field should contain "user_1@deskpro.dev,user_2@deskpro.dev"
