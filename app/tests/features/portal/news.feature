Feature: News
  Clicking around the news, starting from the homepage

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  @reinstall @basic
  Scenario: I visit the News from the homepage
    Given I am on "/"
    When I follow "News"
    Then I should be on "/news"
    And the response status code should be 200

  @reinstall @basic
  Scenario: I visit a category from the News page
    Given I am on "/news"
    When I follow "General"
    Then I should be on "/news/general"
    And the response status code should be 200

  @reinstall @basic
  Scenario: I visit a download from the browse page
    Given I am on "/news/general"
    When I follow "Example News Post"
    Then I should be on "/news/posts/example-news-post"
    And the response status code should be 200
