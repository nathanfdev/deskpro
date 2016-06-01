Feature: To prevent ticket submitting abuse
  Anti-abuse system should work

  Background:
    Given I install the fresh data set
    And rate limit table is empty
    And the following languages are enabled:
      | default |

  Scenario: Checking lockout response for user
    Given I am authenticated as "user"
    And I set "submit_ticket" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time
    And I am on "/new-ticket"
    And I press "Submit"
    Then I should be on "/new-ticket"
    Then I should see "You have submitted too many tickets to this moment and were locked out." in the ".inline-form-alert" element

  Scenario: Checking captcha response for user
    Given I am authenticated as "user"
    And I set "submit_ticket" rate limit to 1 attempt within 15 minutes with "captcha" response
    And I am on "/new-ticket"
    And I press "Submit"
    Then I should be on "/new-ticket"
    And I press "Submit"
    Then I should be on "/new-ticket"
    And I should see an "#ticket_captcha_captcha_auto_added_captcha" element

  Scenario: Checking lockout response for guest
    Given I am not logged in
    And I set "submit_ticket" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time for guest
    And I am on "/new-ticket"
    And I press "Submit"
    Then I should be on "/new-ticket"
    Then I should see "You have submitted too many tickets to this moment and were locked out." in the ".inline-form-alert" element

  Scenario: Checking lockout captcha for guest
    Given I am not logged in
    And I set "submit_ticket" rate limit to 1 attempt within 15 minutes with "captcha" response for guest
    And I am on "/new-ticket"
    And I press "Submit"
    Then I should be on "/new-ticket"
    And I press "Submit"
    Then I should be on "/new-ticket"
    And I should see an "#ticket_captcha_captcha_auto_added_captcha" element