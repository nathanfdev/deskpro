Feature: KB
  Clicking around the knowledgebase, starting from the homepage

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And there are no articles in the Knowledge Base
    And the KB root category "General" exists
    And I add "Example Article" article

  Scenario: I navigate to a KB article from homepage
    Given I am on "/"
    And I follow "Knowledgebase"
    And I follow "General"
    When I follow "Example Article"
    Then I should be on "/kb/articles/example-article"
    And the response status code should be 200

  Scenario: I download a pdf version of an article
    Given I am on "/kb/articles/example-article"
    And I download "Download PDF"
    Then print last response
    And print last response headers
    And the response status code should be 200
    And I should see in the header "content-type":"application/pdf"