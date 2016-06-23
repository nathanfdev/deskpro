@new @custom-fields
Feature: User profile form custom fields

  Background:
    Given I'm authenticated as user

  Scenario: I check custom fields exist on the form
    Given only the following custom person fields exist:
      | #              | Type     | Title          |
      | text_field     | text     | Text field     |
      | textarea_field | textarea | Textarea field |
    And I go to "/profile"

    Then I should see the "person_profile[{text_field}][data]" field
    Then I should see the "person_profile[{textarea_field}][data]" field

  Scenario: I check saving custom fields
    Given only the following custom person fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And I go to "/profile"

    When I fill in "person_profile[{text_field}][data]" with "12345"
    And I press "Save"
    Then I should see a "success" flash message with the phrase "portal.flashes.user_updated_profile"

    When I reload the page
    Then the "person_profile[{text_field}][data]" field should contain "12345"

  Scenario: I check custom fields validation
    Given only the following custom person fields exist:
      | #          | Type | Title      | Options                              |
      | text_field | text | Text field | {"required": true, "min_length": 10} |
    And I go to "/profile"

    When I fill in "person_profile[{text_field}][data]" with "12345"
    And I press "Save"
    Then I should see a form error with the phrase "This value should have 10 characters or more"
