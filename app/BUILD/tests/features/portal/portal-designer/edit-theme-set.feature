Feature: Buffering portal changes in the edit ThemeSet

  Background:
    Given I install the fresh data set
    And the default brand is using the standard theme
    And I am authenticated as admin

  Scenario: I discard edit ThemeSet changes
    When I send a GET request to "/portal/api/style/edit-theme-set/discard"
    Then the response status code should be 200

  Scenario: I commit edit ThemeSet changes
    When I send a GET request to "/portal/api/style/edit-theme-set/commit"
    Then the response status code should be 200

  Scenario: I preview a custom header
    When I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "Just edited custom header"
      }
    """
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/admin-preview"
    Then I should see "Just edited custom header"

  Scenario: I check portal doesn't contain a not yet committed custom header
    When I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "Just edited custom header"
      }
    """
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/"
    Then I should not see "Just edited custom header"

  Scenario: I check portal contain committed custom header
    When I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "Just edited custom header I commit"
      }
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/portal/api/style/edit-theme-set/commit"
    Then the response status code should be 200

    When I send a GET request to "/"
    Then I should see "Just edited custom header I commit"

  Scenario: I discard a custom header
    When I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "Just edited custom header I discard"
      }
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/portal/api/style/edit-theme-set/discard"
    Then the response status code should be 200

    When I send a GET request to "/admin-preview"
    Then I should not see "Just edited custom header I discard"


