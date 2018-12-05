@new @custom-fields
Feature: New feedback form custom fields

  Background:
    Given I'm authenticated as user
    And no Feedback records exist
    And only the following FeedbackCategory records exist:
      | #  | Title      |
      | fc1 | Category 1 |
      | fc2 | Category 2 |
      | fc3 | Category 3 |
    And I grant the "{fc1}" feedback category permission for usergroup everyone
    And I grant the "{fc2}" feedback category permission for usergroup everyone
    And I grant the "{fc3}" feedback category permission for usergroup everyone
    And I set permission "feedback.use" = 1 for "everyone" usergroup

  Scenario: I check custom fields exist on the form
    Given only the following custom feedback fields exist:
      | #              | Type     | Title          |
      | text_field     | text     | Text field     |
      | textarea_field | textarea | Textarea field |

    When I go to "/feedback"
    Then I should see the "new_feedback[custom_data][{text_field}][data]" field
    And I should see the "new_feedback[custom_data][{textarea_field}][data]" field

  Scenario: I check custom field w/o validation
    Given only the following custom feedback fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And I go to "/feedback"

    When I select "Category 1" from "new_feedback_category"
    And I fill in "new_feedback_title" with "Title"
    And I fill in "new_feedback_content" with "I need to report the following bug. It happens when..."
    And I fill in "new_feedback[custom_data][{text_field}][data]" with "12345"
    And I press "Add your feedback"
    Then I should not see a form error with the phrase "This value should have "

  Scenario: I check custom fields validation
    Given only the following custom feedback fields exist:
      | #          | Type | Title      | Options                              |
      | text_field | text | Text field | {"required": true, "min_length": 10} |
    And I go to "/feedback"

    When I select "Category 2" from "new_feedback_category"
    And I fill in "new_feedback_title" with "Title"
    And I fill in "new_feedback_content" with "I need to report the following bug. It happens when..."
    And I fill in "new_feedback[custom_data][{text_field}][data]" with "12345"
    And I press "Add your feedback"
    Then I should see a form error with the phrase "This value should have 10 characters or more"
