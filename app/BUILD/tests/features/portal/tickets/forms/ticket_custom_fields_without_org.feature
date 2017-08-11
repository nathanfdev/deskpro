@new
Feature: Render org custom fields on full ticket form w/ a person w/o an org

  Background:
    Given I'm authenticated as admin
    And the following languages are enabled:
      | default |
    And I have only default brand
    And the "{defaultBrand}" setting "core_tickets.use_ref" is set to "1"
    And a user with "user_1@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And only the following Ticket records exist:
      | #        | Person  | Department | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {admin} | {d2}       | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person  | Message |
      | {ticket_1} | {admin} | text    |
    And only the following custom organization fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And the only default ticket layout exists with fields:
      | user_layout            |
      | org_field_{text_field} |

  Scenario Outline: I open the new ticket form
    When I go to "<route>"
    Then the response status code should be 200
    And I should see ".js_form_tpl" form fields in following order:
      | name                                 |
      | ticket[org_field_{text_field}][data] |

    Examples:
      | route             |
      | /new-ticket       |
      | /tickets/ref/edit |
