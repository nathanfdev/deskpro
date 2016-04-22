Feature: Article ratings
  Users rating articles

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I have "Example Article" article

  @reinstall
  Scenario: I rate an article positively as a user
    Given I login with user credentials
    And I am on "/kb/articles/example-article"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  Scenario: I rate an article negatively as a user
    Given I login with user credentials
    And I am on "/kb/articles/example-article"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"

  Scenario: I rate an article positively as a GUEST
    Given I am on "/kb/articles/example-article"
    When I follow "This page was helpful"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message
    And I should see "You marked this page as helpful"

  Scenario: I rate an article negatively as a GUEST
    Given I am on "/kb/articles/example-article"
    When I follow "This page was not helpful"
    And I press "Continue"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message
    And I should see "You marked this page as unhelpful"
