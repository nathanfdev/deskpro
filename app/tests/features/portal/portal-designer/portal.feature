Feature: Serving custom data on the portal

  Background: Fresh database
    Given I install the fresh data set

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

  @mink:goutte @basic
  Scenario: I check custom header is applied to the portal
    Given I am authenticated as admin
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "Custom Header"
      }
    """
    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
    When I send a GET request to "/en"
    Then I should see "Custom Header"

  @mink:goutte @basic
  Scenario: I check custom footer is applied to the portal
    Given I am authenticated as admin
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "footer": "Custom Footer"
      }
    """
    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
    When I send a GET request to "/en"
    Then I should see "Custom Footer"

  @mink:goutte @basic
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

  Scenario: I retrieve a custom asset
    Given I am authenticated as admin
    And I send the "text.txt" file as "file" to "/portal/api/style/edit-theme-set/assets"
    And I send a GET request to "/portal/api/style/edit-theme-set/commit"
    When I send a GET request to just uploaded file URL
    Then the response status code should be 200
    And the response should contain "Test text file"
