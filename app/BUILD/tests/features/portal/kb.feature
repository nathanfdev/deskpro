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

  Scenario: I navigate to a KB article with everyone usergroup disabled
    Given everyone user group disabled
    When I am on "/"
    Then I should not see "Knowledgebase"
    When I am on "/kb/articles/example-article"
    Then I should be on "/login"

  Scenario: I navigate to a KB article with everyone usergroup disabled
    Given everyone user group disabled
    When I am on "/"
    Then I should not see "Knowledgebase"
    When I am on "/kb/articles/example-article"
    Then I should be on "/login"

  Scenario: I navigate to a KB article as participant of usergroup
    Given everyone user group disabled
    And registered user group disabled
    And testo user group exists
    And I grant the KB_general KB category permission for usergroup testo
    And I login with user credentials
    When I am on "/"
    Then I should not see "Knowledgebase"
    When I am on "/kb/articles/example-article"
    Then the response status code should be 403

    When I add me usergroup relation testo
    And I am on "/"
    Then I follow "Knowledgebase"
    And I follow "General"
    When I follow "Example Article"
    Then I should be on "/kb/articles/example-article"
    And the response status code should be 200
