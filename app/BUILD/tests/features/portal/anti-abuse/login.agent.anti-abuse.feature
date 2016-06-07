Feature: Login anti-abuse feature
  In order to prevent anonymous hacker to guess
  user's password with bruteforce method
  anti-abuse system should work

  Background:
    Given I install the "fresh" data set
    Given rate limit table is empty
    And the following languages are enabled:
      | default |

  Scenario: Checking lockout response for agent/admin
    Given I set "login" rate limit to 5 attempts within 15 minutes with "lockout" response and 15 minutes lockout time
    And I set "login" rate limit to 5 attempts within 15 minutes with "lockout" response and 15 minutes lockout time for guest
    And  I set "login" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time for agent

    When I use bad "admin" credentials for login
    Then I should be on "/login"
    And I should see "You have failed login too many times"

    When I use bad "user" credentials for login
    Then I should be on "/login"
    And I should not see "You have failed login too many times"

    When I use bad credentials for login
    Then I should be on "/login"
    And I should not see "You have failed login too many times"

  Scenario: Checking captcha response for admin/agent
    Given I set "login" rate limit to 5 attempts within 1 minute with "captcha" response
    And I set "login" rate limit to 5 attempts within 1 minute with "captcha" response for guest
    And I set "login" rate limit to 1 attempt within 1 minute with "captcha" response for agent

    When I use bad "agent" credentials for login
    Then I should be on "/login"
    And I should see an "#deskpro_captcha_captcha" element

    When I use bad "user" credentials for login
    Then I should be on "/login"
    And I should not see an "#deskpro_captcha_captcha" element

  Scenario: I'm successfully log in after failure with captcha
    Given I set "login" rate limit to 2 attempts within 1 minute with "captcha" response for agent

    When I use bad "admin" credentials for login
    Then I should be on "/login"

    When I use bad "agent" credentials for login
    And I should not see "You have failed login too many times"

    When I login with "admin" credentials from the login page
    Then I should be on "/"

  Scenario: I'm successfully log in after failure with lockout
    Given I set "login" rate limit to 2 attempts within 1 minute with "lockout" response and 15 minutes lockout time for agent
    When I use bad "agent" credentials for login
    Then I should be on "/login"
    When I login with "agent" credentials from the login page
    Then I should be on "/"
