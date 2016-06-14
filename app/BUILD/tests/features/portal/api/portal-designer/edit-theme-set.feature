Feature: Buffering portal changes in the edit ThemeSet

  Background:
    Given I install the fresh data set
    And I'm authenticated as admin

  Scenario: I check SCSS variable custom value is applied to the portal
    When I send a PUT request to "/portal/api/style/edit-theme-set/variable-values" with body:
    """
{
  "font-family-sans-serif": "Sans Serif Test Font"
}
    """
    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
    Then the portal theme set css should contain "Sans Serif Test Font"

  Scenario: I retrieve a custom asset
    And I send the "text.txt" file as "file" to "/portal/api/style/edit-theme-set/assets"
    When I send a GET request to "/portal/api/style/edit-theme-set/commit"
    Then created blob content should contain "Test text file"

  Scenario: I discard edit ThemeSet changes
    When I send a GET request to "/portal/api/style/edit-theme-set/discard"
    Then the response status code should be 200

  Scenario: I commit edit ThemeSet changes
    When I send a GET request to "/portal/api/style/edit-theme-set/commit"
    Then the response status code should be 200
