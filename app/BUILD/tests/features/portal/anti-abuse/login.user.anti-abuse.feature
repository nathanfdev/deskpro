Feature: Login anti-abuse feature
  In order to prevent anonymous hacker to guess
  user's password with bruteforce method
  anti-abuse system should work

  Background:
    Given I install the fresh data set
    And rate limit table is empty
    And the following languages are enabled:
      | default |

  Scenario: Checking lockout response for user
    Given I set "login" rate limit to 1 attempt within 1 minute with "lockout" response and 15 minutes lockout time
    Given I set "login" rate limit to 5 attempt within 1 minute with "lockout" response and 15 minutes lockout time for agent
    Given I set "login" rate limit to 5 attempt within 1 minute with "lockout" response and 15 minutes lockout time for guest
    When I use bad "user" credentials for login
    Then I should be on "/login"
    When I use bad "user" credentials for login
    Then I should be on "/login"
    And I should see "You have failed login too many times" in the ".inline-form-alert" element
    When I use bad "agent" credentials for login
    Then I should be on "/login"
    And I should not see "You have failed login too many times" in the ".inline-form-alert" element

  Scenario: Checking captcha response for user
    Given I set "login" rate limit to 1 attempt within 1 minute with "captcha" response
    Given I set "login" rate limit to 5 attempts within 1 minute with "captcha" response for agent
    Given I set "login" rate limit to 5 attempts within 1 minute with "captcha" response for guest
    When I use bad "user" credentials for login
    Then I should be on "/login"
    And I should see an "#deskpro_captcha_captcha" element



