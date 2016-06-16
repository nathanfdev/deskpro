@new @custom-fields
Feature: New feedback form custom fields

  Background:
    Given I have usergroups
    And only the following User records exist:
      | #    | Email            |
      | user | user@deskpro.dev |
    And I am authenticated as user

  Scenario: I check custom fields exist on the form
    Given only the following custom feedback fields exist:
      | #              | Type     | Title          |
      | text_field     | text     | Text field     |
      | textarea_field | textarea | Textarea field |

    When I go to "/feedback"
    Then I should see the "new_feedback[custom_data][{text_field}][data]" field
    Then I should see the "new_feedback[custom_data][{textarea_field}][data]" field

  Scenario: I check custom field w/o validation
    Given only the following custom feedback fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And I go to "/feedback"

    When I select "Suggestion" from "new_feedback_category"
    And I fill in "new_feedback_title" with "Title"
    And I fill in "new_feedback_content" with "I need to report the following bug. It happens when..."
    And I fill in "new_feedback[custom_data][{text_field}][data]" with "12345"
    And I press "Add your feedback"
    Then I should not see a form error with the phrase "portal.forms.error_too_short"

  Scenario: I check custom fields validation
    Given only the following custom feedback fields exist:
      | #          | Type | Title      | Options                              |
      | text_field | text | Text field | {"required": true, "min_length": 10} |
    And I go to "/feedback"

    When I select "Suggestion" from "new_feedback_category"
    And I fill in "new_feedback_title" with "Title"
    And I fill in "new_feedback_content" with "I need to report the following bug. It happens when..."
    And I fill in "new_feedback[custom_data][{text_field}][data]" with "12345"
    And I press "Add your feedback"
    Then I should see a form error with the phrase "portal.forms.error_too_short"
