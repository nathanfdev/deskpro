Feature: Adding custom header, footer, SCSS and javascript

  Background: Fresh database
    Given I install the fresh data set
    And I'm authenticated as admin

  Scenario: I get current advanced edit data
    When I go to "/portal/api/style/edit-theme-set/advanced-edits"
    Then the response status code should be 200
    And the JSON node "header" should exist
    And the JSON node "footer" should exist
    And the JSON node "main_scss" should exist
    And the JSON node "custom_scss" should exist
    And the JSON node "javascript" should exist

  Scenario: I modify header, footer, SCSS and javascript
    When I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "header": "header", "footer": "footer", "custom_scss": "div {color: #fff;}", "javascript": "javascript"
}
    """
    Then the response status code should be 204

  Scenario: I retrieve modified header, footer, SCSS and javascript
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "header": "<h1>Header</h1>",
  "footer": "<i>Footer</i>",
  "main_scss": "body {background: white;}",
  "custom_scss": "body {color: red;}",
  "javascript": "alert('hello')"
}
    """
    When I go to "/portal/api/style/edit-theme-set/advanced-edits"
    Then the response status code should be 200
    And the JSON node "header" should be equal to "<h1>Header</h1>"
    And the JSON node "footer" should be equal to "<i>Footer</i>"
    And the JSON node "main_scss" should be equal to "body {background: white;}" raw value
    And the JSON node "custom_scss" should be equal to "body {color: red;}" raw value
    And the JSON node "javascript" should be equal to "alert('hello')"

  Scenario: I preview a custom header
    When I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "header": "Just edited custom header"
}
    """
    Then the response status code should be 204
    And the response should be empty
    When I go to "/admin-preview"
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
    When I go to "/"
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

    When I go to "/portal/api/style/edit-theme-set/commit"
    Then the response status code should be 200

    When I go to "/"
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

    When I go to "/portal/api/style/edit-theme-set/discard"
    Then the response status code should be 200

    When I go to "/admin-preview"
    Then I should not see "Just edited custom header I discard"

  Scenario: I check custom SCSS is applied to the portal
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "custom_scss": ".my-important-custom-css-class {color: red;}"
}
    """
    And I send a PUT request to "/portal/api/style/edit-theme-set/variable-values"
    And I go to "/portal/api/style/edit-theme-set/commit"
    Then the portal theme set css should contain ".my-important-custom-css-class"

  Scenario: I check custom JS is applied to the portal
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
{
  "javascript": "var custom_js = 1 + 1;"
}
    """
    And I go to "/portal/api/style/edit-theme-set/commit"
    When I go to "/"
    Then the response should contain "var custom_js = 1 + 1;"
