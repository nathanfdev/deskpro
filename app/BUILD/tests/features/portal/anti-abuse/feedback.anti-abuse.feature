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
    And I fill in the following:
      | new_feedback[title]                                    | feedback title   |
      | new_feedback[content]                                  | feedback content |
      | new_feedback[custom_data][custom_feedback_def_1][data] | 2                |
    And I press "Add your feedback"
    Then the url should match "/feedback"

    When I am on "/feedback"
    Then I should see "You have submitted feedback too many times."

  Scenario: Checking captcha response for user
    Given I am authenticated as "user"
    And I set "submit_feedback" rate limit to 1 attempt within 15 minutes with "captcha" response
    And I am on "/feedback"

    When I select "Suggestion" from "new_feedback_category"
    And I fill in the following:
      | new_feedback[title]                                    | feedback title   |
      | new_feedback[content]                                  | feedback content |
      | new_feedback[custom_data][custom_feedback_def_1][data] | 2                |
    And I press "Add your feedback"
    Then the url should match "/feedback"

    When I am on "/feedback"
    Then I should see an "#new_feedback_captcha_captcha" element

  Scenario: Checking lockout response for guest
    Given I am not logged in
    And I set "submit_feedback" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time for guest
    And I am on "/feedback"

    When I select "Suggestion" from "new_feedback_category"
    And I fill in the following:
      | new_feedback[name]                                     | Tyrion Lannister                        |
      | new_feedback[email][email]                             | ohmylion@kingslanding.westeros          |
      | new_feedback[title]                                    | About debts                             |
      | new_feedback[content]                                  | A Lannister should always pay his debts |
      | new_feedback[custom_data][custom_feedback_def_1][data] | 2                                       |
    And I press "Add your feedback"
    Then the url should match "/feedback"

    When I am on "/feedback"
    Then I should see "You have submitted feedback too many times."

  Scenario: Checking lockout captcha for guest
    Given I am not logged in
    And I set "submit_feedback" rate limit to 1 attempt within 15 minutes with "captcha" response for guest
    And I am on "/feedback"

    When I select "Suggestion" from "new_feedback_category"
    And I fill in the following:
      | new_feedback[name]                                     | Eddard Stark            |
      | new_feedback[email][email]                             | ned@winterfell.westeros |
      | new_feedback[title]                                    | Let's prepare           |
      | new_feedback[content]                                  | Winter is coming        |
      | new_feedback[custom_data][custom_feedback_def_1][data] | 2                       |
    When I press "Add your feedback"
    Then the url should match "/feedback"

    When I am on "/feedback"
    And I should see an "#new_feedback_captcha_captcha" element
