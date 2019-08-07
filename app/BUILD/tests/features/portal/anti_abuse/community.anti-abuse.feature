Feature: To prevent community topics submitting abuse
  Anti-abuse system should work

  Background:
    Given I install the fresh data set
    And rate limit table is empty
    And the following languages are enabled:
      | default |

  Scenario: Checking lockout response for user
    Given I'm authenticated as "user"
    And I set "submit_community_topic" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time
    And I am on "/community"

    When I select "Suggestion" from "new_community_topic_channel"
    And I fill in the following:
      | new_community_topic[title]                | topic title   |
      | new_community_topic[content]              | topic content |
    And I press "Add your community topic"
    Then the url should match "/community"

    When I am on "/community"
    Then I should see "You have submitted community topics too many times so you have been locked out. Please try again later."

  Scenario: Checking captcha response for user
    Given I'm authenticated as "user"
    And I set "submit_community_topic" rate limit to 1 attempt within 15 minutes with "captcha" response
    And I am on "/community"

    When I select "Suggestion" from "new_community_topic_channel"
    And I fill in the following:
      | new_community_topic[title]                | topic title   |
      | new_community_topic[content]              | topic content |
    And I press "Add your community topic"
    Then the url should match "/community"

    When I am on "/community"
    Then I should see an "#new_community_topic_captcha_captcha" element

  Scenario: Checking lockout response for guest
    Given I am not logged in
    And I set "submit_community_topic" rate limit to 1 attempt within 15 minutes with "lockout" response and 15 minutes lockout time for guest
    And I am on "/community"

    When I select "Suggestion" from "new_community_topic_channel"
    And I fill in the following:
      | new_community_topic[name]                 | Tyrion Lannister                        |
      | new_community_topic[email][email]         | ohmylion@kingslanding.westeros          |
      | new_community_topic[title]                | About debts                             |
      | new_community_topic[content]              | A Lannister should always pay his debts |
    And I press "Add your community topic"
    Then the url should match "/community"

    When I am on "/community"
    Then I should see "You have submitted community topics too many times so you have been locked out. Please try again later."

  Scenario: Checking lockout captcha for guest
    Given I am not logged in
    And I set "submit_community_topic" rate limit to 1 attempt within 15 minutes with "captcha" response for guest
    And I am on "/community"

    When I select "Suggestion" from "new_community_topic_channel"
    And I fill in the following:
      | new_community_topic[name]                 | Eddard Stark            |
      | new_community_topic[email][email]         | ned@winterfell.westeros |
      | new_community_topic[title]                | Let's prepare           |
      | new_community_topic[content]              | Winter is coming        |
    When I press "Add your community topic"
    Then the url should match "/community"

    When I am on "/community"
    And I should see an "#new_community_topic_captcha_captcha" element
