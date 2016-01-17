Feature: Serving custom data on the portal

  Scenario: I check SCSS variable custom value is applied to the portal
    Given I am authenticated as admin
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

  Scenario: I check custom SCSS is applied to the portal
    Given I am authenticated as admin
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "scss": ".my-important-custom-css-class {color: red;}"
      }
    """
    And I send a PUT request to "/portal/api/style/edit-theme-set/variable-values" with body:
    """
    {
    }
    """
    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
    When I send a GET request to "/portal/api/style/portal.css"
    Then the response should contain ".my-important-custom-css-class"

  # ToDo: This is waiting of DB templates don't get rendered in test session

#  Scenario: I check custom header is applied to the portal
#    Given I am authenticated as admin
#    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
#    """
#      {
#        "header": "Custom Header"
#      }
#    """
#    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
#    When I send a GET request to "/"
#    Then I should see "Custom Header"

  # ToDo: This is waiting of DB templates don't get rendered in test session

#  Scenario: I check custom footer is applied to the portal
#    Given I am authenticated as admin
#    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
#    """
#      {
#        "footer": "Custom Footer"
#      }
#    """
#    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
#    When I send a GET request to "/"
#    Then I should see "Custom Footer"

  Scenario: I check custom JS is applied to the portal
    Given I am authenticated as admin
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "javascript": "var custom_js = 1 + 1;"
      }
    """
    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
    When I send a GET request to "/en"
    Then the response should contain "var custom_js = 1 + 1;"

  # ToDo: check assets are served when file upload step is done

