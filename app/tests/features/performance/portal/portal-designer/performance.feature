Feature: Portal designer performance

  Scenario: I recompile Portal styles
    Given I am authenticated as admin
    And I started a timer for the "Stylesheet recompilation" process
    When I send a PUT request to "/portal/api/style/edit-theme-set/variable-values" with body:
    """
{}
    """
    Then the timer should not exceed 15 seconds
    And the response status code should be 204
