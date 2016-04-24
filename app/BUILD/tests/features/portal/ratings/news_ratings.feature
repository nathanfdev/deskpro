Feature: News ratings
  Users rating news

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I have "Example News Post" news

  Scenario: I rate a news post positively as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  Scenario: I rate a news post negatively as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"

  Scenario: I rate a news post positively as a GUEST
    Given I am on "/news/posts/example-news-post"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  Scenario: I rate a news post negatively as a user
    Given I am on "/news/posts/example-news-post"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"
