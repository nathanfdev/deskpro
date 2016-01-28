Feature: Adding custom header, footer, SCSS and javascript

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I get current advanced edit data
    Given I am authenticated as admin
    When I send a GET request to "/portal/api/style/edit-theme-set/advanced-edits"
    Then the response status code should be 200
    And the JSON node "header" should exist
    And the JSON node "footer" should exist
    And the JSON node "scss" should exist
    And the JSON node "javascript" should exist

  Scenario: I modify header, footer, SCSS and javascript
    Given I am authenticated as admin
    When I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "header", "footer": "footer", "scss": "scss", "javascript": "javascript"
      }
    """
    Then the response status code should be 204

  Scenario: I retrieve modified header, footer, SCSS and javascript
    Given I am authenticated as admin
    And I send a PUT request to "/portal/api/style/edit-theme-set/advanced-edits" with body:
    """
      {
        "header": "<h1>Header</h1>",
        "footer": "<i>Footer</i>",
        "scss": "body {color: red;}",
        "javascript": "alert('hello')"
      }
    """
    When I send a GET request to "/portal/api/style/edit-theme-set/advanced-edits"
    Then the response status code should be 200
    And the JSON node "header" should be equal to "<h1>Header</h1>"
    And the JSON node "footer" should be equal to "<i>Footer</i>"
    And the JSON node "scss" should be equal to "body {color: red;}"
    And the JSON node "javascript" should be equal to "alert('hello')"