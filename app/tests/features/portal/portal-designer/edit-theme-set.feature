Feature: Buffering portal changes in the edit ThemeSet

  Scenario: I discard edit ThemeSet changes
    Given I am authenticated as admin
    When I send a GET request to "/portal/api/style/edit-theme-set/discard"
    Then the response status code should be 200

  Scenario: I commit edit ThemeSet changes
    Given I am authenticated as admin
    When I send a GET request to "/portal/api/style/edit-theme-set/commit"
    Then the response status code should be 200

  # ToDo: This is waiting of DB templates don't get rendered in test session

#  Scenario: I preview a custom header
#    Given I am authenticated as admin
#    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
#    """
#      {
#        "header": "Custom Header"
#      }
#    """
#    When I send a GET request to "/admin-preview/"
#    Then I should see "Custom Header"

  Scenario: I check portal doesn't contain a not yet committed custom header
    Given I am authenticated as admin
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "Custom Header"
      }
    """
    When I send a GET request to "/"
    Then I should not see "Custom Header"

  Scenario: I discard a custom header
    Given I am authenticated as admin
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "Custom Header"
      }
    """
    And I send a GET request to "/portal/api/style/edit-theme-set/discard"
    When I send a GET request to "/admin-preview/"
    Then I should not see "Custom Header"


