Feature: To prevent registration spam
  Anti-abuse system should work

  Scenario: Checking lockout response
    Given I set "registration" rate limit to 1 attempts within 1 minute with "lockout" response and 15 minutes lockout time
    Given I am on "/register"
    When I fill in "person_registration_primary_email_email" with "111"
    And I press "Register"
    Then I should see "You have registered too many times." in the ".inline-form-alert" element

  Scenario: Checking captcha response
    Given I set "registration" rate limit to 1 attempts within 1 minute with "captcha" response
    And rate limit table is empty
    Given I am on "/register"
    When I fill in "person_registration_primary_email_email" with "111"
    And I press "Register"
    Then I should be on "/register"
    When I fill in "person_registration_primary_email_email" with "111"
    And I press "Register"
    And I should see an "#person_registration_captcha_captcha" element