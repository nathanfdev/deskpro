Feature: News subscriptions
  Users can subscribe to news and news categories

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I have "Example News Post" news

  @reinstall
  Scenario: I subscribe to a news category successfully as a user
    Given I login with user credentials
    And I am on "/news/general"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/news/general"
    And user should be subscribed to the news category "General"
    And I should see a "success" flash message
    And I should see "Unsubscribe"

  Scenario: I subscribe to a news post successfully as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And user should be subscribed to the news content "Example News Post"
    And I should see a "success" flash message

  Scenario: I cannot subscribe to a news category as a GUEST
    Given I am on "/news/general"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  Scenario: I cannot subscribe to a news post as a GUEST
    Given I am on "/news/posts/example-news-post"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200
