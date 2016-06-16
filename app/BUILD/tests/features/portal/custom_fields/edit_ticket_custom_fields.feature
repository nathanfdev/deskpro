@new @custom-fields
Feature: Edit ticket form custom fields

  Background:
    Given a user with "user@deskpro.dev" email exists
    And I am authenticated as user
    And only the following Ticket records exist:
      | #        | Person             | Ref | Subject        | Date Last User Reply |
      | ticket_1 | {user@deskpro.dev} | ref | Ticket Subject | 2015-06-15 17:00:00  |
    And only the following TicketMessage records exist:
      | Ticket     | Person             | Message |
      | {ticket_1} | {user@deskpro.dev} | text    |

  Scenario: I check custom fields exist on the form
    Given only the following custom ticket fields exist:
      | #              | Type     | Title          |
      | text_field     | text     | Text field     |
      | textarea_field | textarea | Textarea field |
    And the only default ticket layout exists with fields:
      | user_layout                   |
      | ticket_field_{text_field}     |
      | ticket_field_{textarea_field} |

    When I go to "/tickets/ref/edit"
    Then I should see the "ticket[ticket_field_{text_field}][data]" field
    Then I should see the "ticket[ticket_field_{textarea_field}][data]" field

  Scenario: I check saving custom fields
    Given only the following custom ticket fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And the only default ticket layout exists with fields:
      | user_layout               |
      | ticket_field_{text_field} |
    And I go to "/tickets/ref/edit"

    When I fill in "ticket[ticket_field_{text_field}][data]" with "12345"
    And I press "Save"
    Then I should not see a form error with the phrase "portal.forms.error_too_short"

    When I reload the page
    Then the "ticket[ticket_field_{text_field}][data]" field should contain "12345"

  Scenario: I check custom fields validation
    Given only the following custom ticket fields exist:
      | #          | Type | Title      | Options                              |
      | text_field | text | Text field | {"required": true, "min_length": 10} |
    And the only default ticket layout exists with fields:
      | user_layout               |
      | ticket_field_{text_field} |
    And I go to "/tickets/ref/edit"

    When I fill in "ticket[ticket_field_{text_field}][data]" with "12345"
    And I press "Save"
    Then I should see a form error with the phrase "portal.forms.error_too_short"
