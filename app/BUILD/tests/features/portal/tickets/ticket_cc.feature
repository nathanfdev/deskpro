@new
Feature: Add and edit ticket participants

  Background:
    Given I'm authenticated as user
    And a user with "user_1@deskpro.dev" email exists
    And a user with "user_2@deskpro.dev" email exists
    And an agent with "agent@deskpro.dev" email exists
    And only the following Ticket records exist:
      | #        | Person | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |
    And the only default ticket layout exists with fields:
      | user_layout |
      | cc          |

  Scenario: I edit ticket with cc
    Given I go to "/tickets/ref/edit"
    And I select "Sales" from "Department"
    And I fill in "Cc" with "user_1@deskpro.dev,user_2@deskpro.dev"
    And I press "Save"
    When I go to "/tickets/ref/edit"
    Then the "Cc" field should contain "user_1@deskpro.dev,user_2@deskpro.dev"

  Scenario: I create new ticket with cc
    Given I go to "/new-ticket"
    And I fill in "Cc" with "user_1@deskpro.dev,user_2@deskpro.dev"
    When I press "Submit"
    Then the "Cc" field should contain "user_1@deskpro.dev,user_2@deskpro.dev"

  Scenario: I check ticket cc validation (not user)
    Given I go to "/new-ticket"
    And I fill in "Cc" with "agent@deskpro.dev"
    When I press "Submit"
    Then the "Cc" field should contain "agent@deskpro.dev"
    And I should see a form error with the phrase "portal.forms.person_not_user"

  Scenario: I check ticket cc validation (not found)
    Given I go to "/new-ticket"
    And I fill in "Cc" with "unkkwon_agent@deskpro.dev"
    When I press "Submit"
    Then the "Cc" field should contain "unkkwon_agent@deskpro.dev"
    And I should see a form error with the phrase "portal.forms.person_not_found"
