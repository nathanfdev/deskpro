Feature: To prevent registration spam
  Anti-abuse system should work

  Background:
    Given I install the fresh data set
    And rate limit table is empty
    And the following languages are enabled:
      | default |

  Scenario: Checking lockout response
    Given I set "registration" rate limit to 1 attempts within 1 minute with "lockout" response and 15 minutes lockout time
    Given I am on "/register"
    When I fill in "person_registration_primary_email_email" with "111"
    And I press "Register"
    Then I should see "You have registered too many times." in the ".inline-form-alert" element

  Scenario: Checking captcha response
    Given I set "registration" rate limit to 1 attempts within 1 minute with "captcha" response
    And I am on "/register"
    When I fill in "person_registration_primary_email_email" with "111"
    And I press "Register"
    Then I should be on "/register"
    When I fill in "person_registration_primary_email_email" with "111"
    And I press "Register"
    And I should see an "#person_registration_captcha_captcha" element