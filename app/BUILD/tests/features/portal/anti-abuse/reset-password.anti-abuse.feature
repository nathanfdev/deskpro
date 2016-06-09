Feature: To prevent password resetting abuse
  Anti-abuse system should work

  Background:
    Given I install the "fresh" data set
    And rate limit table is empty
    And the following languages are enabled:
      | default |

  Scenario: Checking lockout response
    Given I set "reset_password" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time
    And I am on "/login/reset-password"

    When I fill in "request_password_reset_email" with "111"

    And I press "Reset Password"
    Given I am on "/login/reset-password"
    Then I should see "You are trying to reset password too often and were locked out." in the ".inline-form-alert" element

  Scenario: Checking captcha response
    Given I set "reset_password" rate limit to 1 attempt within 15 minutes with "captcha" response
    And I am on "/login/reset-password"
    When I fill in "request_password_reset_email" with "111"
    And I press "Reset Password"
    Given I am on "/login/reset-password"
    Then I should see an "#request_password_reset_captcha_captcha" element