@new @custom-fields
Feature: New ticket form custom fields

  Background:
    Given a user with "user@deskpro.dev" email exists
    And I am authenticated as user

  Scenario: I check custom fields exist on the form
    Given only the following custom ticket fields exist:
      | #              | Type     | Title          |
      | text_field     | text     | Text field     |
      | textarea_field | textarea | Textarea field |
    And the only default ticket layout exists with fields:
      | user_layout                   |
      | ticket_field_{text_field}     |
      | ticket_field_{textarea_field} |
    And I go to "/new-ticket"

    Then I should see the "ticket[ticket_field_{text_field}][data]" field
    Then I should see the "ticket[ticket_field_{textarea_field}][data]" field

  Scenario: I check submitting custom fields w/o validation
    Given only the following custom ticket fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And the only default ticket layout exists with fields:
      | user_layout               |
      | ticket_field_{text_field} |
    And I go to "/new-ticket"

    When I fill in "ticket[ticket_field_{text_field}][data]" with "12345"
    And I press "Submit"
    Then I should not see a form error with the phrase "portal.forms.error_too_short"

  Scenario: I check custom fields validation
    Given only the following custom ticket fields exist:
      | #          | Type | Title      | Options                              |
      | text_field | text | Text field | {"required": true, "min_length": 10} |
    And the only default ticket layout exists with fields:
      | user_layout               |
      | ticket_field_{text_field} |
    And I go to "/new-ticket"

    When I fill in "ticket[ticket_field_{text_field}][data]" with "12345"
    And I press "Submit"
    Then I should see a form error with the phrase "portal.forms.error_too_short"
