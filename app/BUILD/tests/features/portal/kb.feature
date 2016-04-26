Feature: KB
  Clicking around the knowledgebase, starting from the homepage

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And there are no articles in the Knowledge Base
    And I add "Example Article" article

  Scenario: I visit the KB from the homepage
    Given I am on "/"
    When I follow "Knowledgebase"
    Then I should be on "/kb"
    And the response status code should be 200

  @reinstall
  Scenario: I visit a category from the KB page
    Given I am on "/kb"
    When I follow "General"
    Then I should be on "/kb/general"
    And the response status code should be 200

  @reinstall
  Scenario: I visit an article from the browse page
    Given I am on "/kb/general"
    When I follow "Example Article"
    Then I should be on "/kb/articles/example-article"
    And the response status code should be 200
