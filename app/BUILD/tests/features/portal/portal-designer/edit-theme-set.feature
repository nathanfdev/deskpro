Feature: Buffering portal changes in the edit ThemeSet

  Background:
    Given I install the fresh data set
    And I am authenticated as admin

  @reinstall
  Scenario: I check SCSS variable custom value is applied to the portal
    And I send a PUT request to "/portal/api/style/edit-theme-set/variable-values" with body:
    """
    {
      "font-default": "Arial Test Font"
    }
    """
    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
    When I send a GET request to "/portal/api/style/portal.css"
    Then the response status code should be 200
    And the response should contain "Arial Test Font"

  Scenario: I retrieve a custom asset
    And I send the "text.txt" file as "file" to "/portal/api/style/edit-theme-set/assets"
    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
    When I send a GET request to just uploaded file URL
    Then the response status code should be 200
    And the response should contain "Test text file"

  Scenario: I discard edit ThemeSet changes
    When I send a GET request to "/portal/api/style/edit-theme-set/discard"
    Then the response status code should be 200

  Scenario: I commit edit ThemeSet changes
    When I send a GET request to "/portal/api/style/edit-theme-set/commit"
    Then the response status code should be 200

