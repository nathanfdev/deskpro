Feature: Editing SCSS variables

  Background:
    Given I install the fresh data set
    And I'm authenticated as admin

  Scenario: I get variables in groups
    When I send a GET request to "/portal/api/style/variable-groups"
    Then the response status code should be 200
    And the JSON node "colors" should exist

  Scenario: I get edit ThemeSet custom variable values
    When I send a GET request to "/portal/api/style/edit-theme-set/variable-values"
    Then the response status code should be 200
    And the JSON node "text-color" should exist
    And the JSON node "link-color" should exist

  Scenario: I retrieve modified variable values
    And I send a PUT request to "/portal/api/style/edit-theme-set/variable-values" with body:
    """
{
  "font-default": "Test Font"
}
    """
    When I send a GET request to "/portal/api/style/edit-theme-set/variable-values"
    Then the response status code should be 200
    And the JSON node "font-default" should be equal to "Test Font"
