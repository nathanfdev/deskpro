@new @custom-fields
Feature: Registration form custom fields

  Background: Fresh database
    Given I disable anti-abuse rate limiting
    And there are no Person records
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: I check custom fields exist on the form
    Given only the following custom person fields exist:
      | #              | Type     | Title          |
      | text_field     | text     | Text field     |
      | textarea_field | textarea | Textarea field |

    When I go to "/register"
    Then I should see the "person_registration[{text_field}][data]" field
    Then I should see the "person_registration[{textarea_field}][data]" field

  Scenario: I check saving custom fields
    Given only the following custom person fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And I go to "/register"
    And I get captcha code from the form field "captcha"

    When I fill in "Name" with "Entering an Existing Email"
    And I fill in "person_registration_primary_email_email" with "user@deskpro.dev"
    And I fill in "Password" with "password"
    And I fill in "Confirm" with "password"
    And I fill in "person_registration[{text_field}][data]" with "12345"
    And I fill in "person_registration[captcha][captcha]" with "{captchaCode}"
    And I press "Register"
    Then I should not see a form error with the phrase "This value should have"

    When I click the email verification link received on "user@deskpro.dev"
    And I go to "/profile"
    Then the "person_profile[{text_field}][data]" field should contain "12345"

  Scenario: I check custom fields validation
    Given only the following custom person fields exist:
      | #          | Type | Title      | Options                              |
      | text_field | text | Text field | {"required": true, "min_length": 10} |
    And I go to "/register"

    And I fill in "Name" with "Entering an Existing Email"
    And I fill in "person_registration_primary_email_email" with "user@deskpro.dev"
    And I fill in "Password" with "password"
    And I fill in "Confirm" with "password"
    And I fill in "person_registration[{text_field}][data]" with "12345"
    And I press "Register"

    Then I should see a form error with the phrase "This value should have 10 characters or more"
