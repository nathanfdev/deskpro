Feature: Adding custom SCSS and javascript

  Background: Fresh database
    Given I install the fresh data set
    And I'm authenticated as admin
    And I have only default brand

  Scenario: I get current advanced edit data
    When I go to "/b/default/portal/api/style/edit-theme-set/advanced-edits"
    Then the response status code should be 200
    And the JSON node "main_scss" should exist
    And the JSON node "custom_scss" should exist
    And the JSON node "javascript" should exist

  Scenario: I modify SCSS and javascript
    When I send a PUT request to "/b/default/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "custom_scss": "div {color: #fff;}", "javascript": "javascript"
}
    """
    Then the response status code should be 204

  Scenario: I retrieve modified SCSS and javascript
    And I send a PUT request to "/b/default/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "main_scss": "body {background: white;}",
  "custom_scss": "body {color: red;}",
  "javascript": "alert('hello')"
}
    """
    When I go to "/b/default/portal/api/style/edit-theme-set/advanced-edits"
    Then the response status code should be 200
    And the JSON node "main_scss" should be equal to "body {background: white;}" raw value
    And the JSON node "custom_scss" should be equal to "body {color: red;}" raw value
    And the JSON node "javascript" should be equal to "alert('hello')"

  Scenario: I check custom SCSS is applied to the portal
    And I send a PUT request to "/b/default/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "custom_scss": ".my-important-custom-css-class {color: red;}"
}
    """
    And I send a PUT request to "/b/default/portal/api/style/edit-theme-set/variable-values"
    And I go to "/b/default/portal/api/style/edit-theme-set/commit"
    Then the portal theme set css should contain ".my-important-custom-css-class"

  Scenario: I check custom JS is applied to the portal
    And I send a PUT request to "/b/default/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "javascript": "var custom_js = 1 + 1;"
}
    """
    And I go to "/b/default/portal/api/style/edit-theme-set/commit"
    When I go to "/"
    Then the response should contain "var custom_js = 1 + 1;"
