Feature: Ratings
  Users rating content

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
  Scenario: I rate an article positively as a user
    Given I login with user credentials
    And I am on "/kb/articles/example-article"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  @reinstall
  Scenario: I rate an article negatively as a user
    Given I login with user credentials
    And I am on "/kb/articles/example-article"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"

  @reinstall
  Scenario: I rate an article positively as a GUEST
    Given I am on "/kb/articles/example-article"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  @reinstall
  Scenario: I rate an article negatively as a GUEST
    Given I am on "/kb/articles/example-article"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"

  #
  # NEWS
  #

  @reinstall
  Scenario: I rate a news post positively as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  @reinstall
  Scenario: I rate a news post negatively as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"

  @reinstall
  Scenario: I rate a news post positively as a GUEST
    Given I am on "/news/posts/example-news-post"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  @reinstall
  Scenario: I rate a news post negatively as a user
    Given I am on "/news/posts/example-news-post"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"

  #
  # FEEDBACK
  #

  @reinstall
  Scenario: I rate a feedback item positively as a user via I AGREE
    Given I login with user credentials
    And the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I follow "I Agree"
    And I press "Continue"
    Then I should be on "/feedback/view/example-feedback"
    And I should see a "success" flash message
    And I should see "Thank you for your feedback!"

  @reinstall
  Scenario: I rate a feedback item positively as a GUEST via I AGREE
    Given the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I follow "I Agree"
    And I press "Continue"
    Then I should be on "/feedback/view/example-feedback"
    And I should see a "success" flash message
    And I should see "Thank you for your feedback!"

  #
  # DOWNLOADS
  #

  @reinstall
  Scenario: I rate a download positively as a user
    Given I login with user credentials
    And the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  @reinstall
  Scenario: I rate a download negatively as a user
    Given I login with user credentials
    And the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"

  @reinstall
  Scenario: I rate a download positively as a GUEST
    Given the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  @reinstall
  Scenario: I rate a download negatively as a GUEST
    Given the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"
