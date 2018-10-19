@new
Feature: Ignore extra fields on new ticket form

  Background:
    Given no Person records exist
    And no Ticket records exist
    And no TicketLayout records exist
    And I have only default brand
    And I'm authenticated as user
    And user has defaultBrand brand
    And the "{defaultBrand}" setting "core_tickets.use_ref" is set to "1"
    And the following languages are enabled:
      | default |
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And a user with "user_1@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And only the following custom ticket fields exist:
      | #  | Type     | Title          |
      | f1 | text     | Text field     |
      | f2 | textarea | Textarea field |
    And the default ticket layout exists with fields:
      | user_layout       |
      | ticket_field_{f1} |
      | ticket_field_{f2} |
    And the ticket layout exists for "{d1}" department with fields:
      | user_layout |
      | cc          |

  Scenario: I try to submit extra fields on edit
    And only the following Ticket records exist:
      | #        | Person | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user} | {d2}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person | Message |
      | {ticket_1} | {user} | text    |
    And I go to "/tickets/ref/edit"

    When I select "Department 1" from "Department"
    And I fill in "ticket[ticket_field_{f1}][data]" with "val 1"
    And I fill in hidden field "ticket[displayed_fields]" with "ticket_field_{f1},cc"
    And I press "Save"

    Then I should be on "/tickets/ref"

  Scenario: I try to submit extra fields on the new ticket form
    When I go to "/new-ticket"
    And I fill in "Subject" with "This is a subject"
    And I select "Department 2" from "Department"
    And I fill in "ticket[ticket_field_{f1}][data]" with "val 1"
    And I press "Submit"
    And I select "Department 1" from "Department"
    And I fill in "Message" with "Here is my ticket message"
    And I fill in hidden field "ticket[displayed_fields]" with "ticket_field_{f1},cc"
    And I press "Submit"

    Then the url should match "/thank-you/[a-zA-Z0-9\-]+"
