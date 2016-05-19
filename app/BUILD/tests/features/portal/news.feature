Feature: News
  Clicking around the news, starting from the homepage

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I have "Example News Post" news

  Scenario: I visit the News from the homepage
    Given I am on "/"
    When I follow "News"
    Then I should be on "/news"
    And the response status code should be 200

  Scenario: I visit a category from the News page
    Given I am on "/news"
    When I follow "General"
    Then I should be on "/news/general"
    And the response status code should be 200

  Scenario: I visit a download from the browse page
    Given I am on "/news/general"
    When I follow "Example News Post"
    Then I should be on "/news/posts/example-news-post-3"
    And the response status code should be 200

  Scenario: I  download a pdf version of a news page
    Given I am on "/news/posts/example-news-post-3"
    When I try to download "Download PDF"
    Then I should see response status code "200"
    And I should see in the header "content-type":"application/pdf"
