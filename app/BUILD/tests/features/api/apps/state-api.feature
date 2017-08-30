@new
Feature: Test Apps State API
  As an API user
  I want to install v2 apps

  Background:
    Given there are no "App" records
    And I'm authenticated as agent
    And I package the app from folder "resources/apps/state-tests"
    And I send a "POST" request to "/api/v2/apps" with content type "application/zip" and file "{lastPackagedApp}" as body
    And I save a reference "application" to the JSON node "id"

  Scenario Outline: I create and delete state
    Given I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 404
    When I send a PUT request to "/api/v2/apps/{application}/state/<entity>/<name>" with body:
    """
{
  "value": "<value>"
}
  """
    Then the response status code should be 200
    When I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 200
    And the JSON node "value" should be equal to "<value>"
    When I send a DELETE request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 200
    Given I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 404

    Examples:
      | name       | value    | entity   |
      | user-only  | Yahoo    | ticket:1 |

  Scenario Outline: I update state
    Given I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 404
    When I send a PUT request to "/api/v2/apps/{application}/state/<entity>/<name>" with body:
    """
{
  "value": "<value>"
}
  """
    When I send a PUT request to "/api/v2/apps/{application}/state/<entity>/<name>" with body:
    """
{
  "value": "updated-<value>"
}
  """
    Then the response status code should be 200
    And the JSON node "value" should be equal to "updated-<value>"
    Then I send a DELETE request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Examples:
      | name       | value    | entity   |
      | user-only  | Yahoo    | ticket:1 |
