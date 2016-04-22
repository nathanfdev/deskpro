Feature: Articles subscriptions
  Users can subscribe to articles and articles categories

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I have "Example Article" article

  @reinstall
  Scenario: I subscribe to an article category successfully as a user
    Given I login with user credentials
    And I am on "/kb/general"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/kb/general"
    And user should be subscribed to the kb category "General"
    And I should see a "success" flash message
    And I should see "Unsubscribe"

  Scenario: I subscribe to an article successfully as a user
    Given I login with user credentials
    And I am on "/kb/articles/example-article"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And user should be subscribed to the kb content "Example Article"
    And I should see a "success" flash message

  Scenario: I cannot subscribe to an article category as a GUEST
    Given I am on "/kb/general"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  Scenario: I cannot subscribe to an article as a GUEST
    Given I am on "/kb/articles/example-article"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200
