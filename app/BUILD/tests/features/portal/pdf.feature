Feature: PDF generation

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: I download a pdf version of an article
    Given I have "Example Article" article
    And I am on "/"
    When I follow "Knowledgebase"
    And I follow "General"
    And I follow "Example Article"
    And I download "Download PDF"
    Then the response status code should be 200
    And I should see in the header "content-type":"application/pdf"

  Scenario: I download a pdf version of a news page
    Given I have "Example News" news
    And I am on "/"
    When I follow "News"
    And I follow "General"
    And I follow "Example News"
    And I download "Download PDF"
    Then the response status code should be 200
    And I should see in the header "content-type":"application/pdf"