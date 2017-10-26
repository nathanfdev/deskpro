@new @apps
Feature: Test Apps State Resolution
  As an API user
  I want to make sure the state security features are working

  Background:
    Given there are no "Person" records
    And there are no "AppState" records
    And there are no "App" records
    And I'm authenticated as agent
    And I package the app from folder "resources/apps/state-tests"
    And I send a "POST" request to "/api/v2/apps" with content type "application/zip" and file "{lastPackagedApp}" as body
    And I save the JSON node "id" as placeholder "application"

    And an agent with "user-test-apps-one@deskpro.com" email exists
    And an agent with "user-test-apps-two@deskpro.com" email exists

  Scenario Outline: I can read a shared variable owned by another user when have not set the same variable
    Given I'm authenticated as person with email "user-test-apps-two@deskpro.com"
    And I send a PUT request to "/api/v2/apps/{application}/state/<entity>/<name>" with body:
    """
{
  "value": "<value>"
}
  """
    Then the response status code should be 200
    When I'm authenticated as person with email "user-test-apps-one@deskpro.com"
    And I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 200
    Then the JSON node "value" should be equal to "<value>"

    # perform cleanup
    Given I'm authenticated as person with email "user-test-apps-two@deskpro.com"
    And I send a DELETE request to "/api/v2/apps/{application}/state/<entity>/<name>"

  Examples:
  | name           | value    | entity   |
  | user-writable  | Yahoo    | ticket:1 |


  Scenario Outline: I will ONLY read a shared variable I own
    Given I'm authenticated as person with email "<user-one>@deskpro.com"
    And I send a PUT request to "/api/v2/apps/{application}/state/<entity>/<name>" with body:
    """
{
  "value": "<user-one>"
}
  """
    And I'm authenticated as person with email "<user-two>@deskpro.com"
    And I send a PUT request to "/api/v2/apps/{application}/state/<entity>/<name>" with body:
    """
{
  "value": "<user-two>"
}
  """
    Given I'm authenticated as person with email "<user-one>@deskpro.com"
    When I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 200
    And the JSON node "value" should be equal to "<user-one>"

    Given I'm authenticated as person with email "<user-two>@deskpro.com"
    When I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 200
    Then the JSON node "value" should be equal to "<user-two>"

    # perform cleanup
    Given I'm authenticated as person with email "user-test-apps-two@deskpro.com"
    And I send a DELETE request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 200

    Examples:
      | name           | user-one             | user-two            | entity   |
      | user-writable  | user-test-apps-one   | user-test-apps-two  | ticket:1 |

  Scenario Outline: I can not read somebody else's private variable
    Given I'm authenticated as person with email "<user-two>@deskpro.com"
    And I send a PUT request to "/api/v2/apps/{application}/state/<entity>/<name>" with body:
    """
{
  "value": "<user-two>"
}
  """
    And I'm authenticated as person with email "<user-one>@deskpro.com"
    When I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 404

    When I'm authenticated as person with email "<user-two>@deskpro.com"
    And I send a GET request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 200
    Then the JSON node "value" should be equal to "<user-two>"

    # perform cleanup
    Given I send a DELETE request to "/api/v2/apps/{application}/state/<entity>/<name>"
    Then the response status code should be 200

    Examples:
      | name           | user-one             | user-two            | entity   |
      | user-only      | user-test-apps-one   | user-test-apps-two  | ticket:1 |


