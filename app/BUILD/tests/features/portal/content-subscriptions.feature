Feature: Subscriptions
  Users can subscribe to content and content categories

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I have "Example Article" article
    And I have "Example News Post" news

  #
  # ARTICLES
  #

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

  @reinstall
  Scenario: I subscribe to an article successfully as a user
    Given I login with user credentials
    And I am on "/kb/articles/example-article"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And user should be subscribed to the kb content "Example Article"
    And I should see a "success" flash message

  @reinstall
  Scenario: I scannot subscribe to an article category as a GUEST
    Given I am on "/kb/general"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  @reinstall
  Scenario: I cannot subscribe to an article as a GUEST
    Given I am on "/kb/articles/example-article"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  #
  # NEWS
  #

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

  @reinstall
  Scenario: I subscribe to a news post successfully as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And user should be subscribed to the news content "Example News Post"
    And I should see a "success" flash message

  @reinstall
  Scenario: I cannot subscribe to a news category as a GUEST
    Given I am on "/news/general"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  @reinstall
  Scenario: I cannot subscribe to a news post as a GUEST
    Given I am on "/news/posts/example-news-post"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  #
  # FEEDBACK
  #

  @reinstall
  Scenario: I subscribe to a feedback item successfully as a user
    Given I login with user credentials
    And the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/feedback/view/example-feedback"
    And user should be subscribed to the feedback content "Example Feedback"
    And I should see a "success" flash message

  @reinstall
  Scenario: I cannot subscribe to a feedback item as a GUEST
    Given the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  #
  # DOWNLOADS
  #

  @reinstall
  Scenario: I subscribe to a download category successfully as a user
    Given I login with user credentials
    And the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/general"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/downloads/general"
    And user should be subscribed to the download category "General"
    And I should see a "success" flash message
    And I should see "Unsubscribe"

  @reinstall
  Scenario: I subscribe to a download successfully as a user
    Given I login with user credentials
    And the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And user should be subscribed to the download content "Example Download"
    And I should see a "success" flash message

  @reinstall
  Scenario: I cannot subscribe to a download category as a GUEST
    Given the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/general"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200

  @reinstall
  Scenario: I cannot subscribe to a download as a GUEST
    Given the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200
