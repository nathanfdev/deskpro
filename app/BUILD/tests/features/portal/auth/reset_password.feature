@new
Feature: Reset password

  Scenario: Submitting with empty email
    Given I go to "/login/reset-password"

    When I press "Reset Password"
    Then "password_reset_request[email]" form field should have error with the phrase "portal.forms.error_required"
    And "password_reset_request[email]" form field should have 1 error

  Scenario: Submitting with bad email
    Given I go to "/login/reset-password"

    When I fill in "Email" with "bad_email"
    And I press "Reset Password"
    Then "password_reset_request[email]" form field should have error with the phrase "bad_email"
    And "password_reset_request[email]" form field should have 1 error
