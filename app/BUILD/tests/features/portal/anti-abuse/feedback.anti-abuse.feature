Feature: To prevent feedback submitting abuse
  Anti-abuse system should work

  Background:
    Given I install the fresh data set
    And rate limit table is empty
    And the following languages are enabled:
      | default |

  Scenario: Checking lockout response for user
    Given I am authenticated as "user"
    And I set "submit_feedback" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time
    And I am on "/feedback"
    When I select "Suggestion" from "new_feedback_category"
    And I press "Add your feedback"
    Then I should be on "/feedback"
    And I should see "You have submitted feedback too many times." in the ".inline-form-alert" element

  Scenario: Checking captcha response for user
    Given I am authenticated as "user"
    And I set "submit_feedback" rate limit to 1 attempt within 15 minutes with "captcha" response
    When I select "Suggestion" from "new_feedback_category"
    And I press "Add your feedback"
    Then I should be on "/feedback"
    When I press "Add your feedback"
    Then I should be on "/feedback"
    And I should see an "#new_feedback_captcha_captcha" element

  Scenario: Checking lockout response for guest
    Given I am not logged in
    And I set "submit_feedback" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time for guest
    And I am on "/feedback"
    When I select "Suggestion" from "new_feedback_category"
    And I press "Add your feedback"
    Then I should be on "/feedback"
    And I should see "You have submitted feedback too many times." in the ".inline-form-alert" element


  Scenario: Checking lockout captcha for guest
    Given I am not logged in
    And I set "submit_feedback" rate limit to 1 attempt within 15 minutes with "captcha" response for guest
    And I am on "/feedback"
    When I select "Suggestion" from "new_feedback_category"
    And I press "Add your feedback"
    Then I should be on "/feedback"
    When I press "Add your feedback"
    Then I should be on "/feedback"
    And I should see an "#new_feedback_captcha_captcha" element