@new @apps
Feature: apps packages api
  As an apps API client
  I want to retrieve information about apps

  Background:
    # We are also creating the first instance of each app when installing
    Given I'm authenticated as admin
    And there are no "App" records
    And I install the app from folder "resources/apps/state-tests"
    And I install the app from folder "resources/apps/simple-app"

  Scenario Outline: I request information about application with a single instance using various IDs
    Given I'm authenticated via session as agent
    When I send a "GET" request to "api/v2/apps/<packageName>"
    Then the response status code should be 200
    Then the JSON node "data.name" should be equal to "<title>"

    Given I save the JSON node "data.id" as placeholder "appId"
    When I send a "GET" request to "api/v2/apps/~appId~"
    Then the response status code should be 200
    Then the JSON node "data.name" should be equal to "<title>"

    Examples:
      | packageName       | title       |
      | @deskproapps/jira |  Jira BETA  |
      | simple-app        |  Simple App |

  Scenario Outline: I request the list of assets for an application with a single instance using various IDs
    Given I'm authenticated via session as agent
    And I send a "GET" request to "api/v2/apps/<packageName>"
    And I save the JSON node "data.id" as placeholder "appId"

    When I send a "GET" request to "api/v2/apps/<packageName>/assets"
    Then the response status code should be 200
    And the JSON node "root" should have 3 elements
    And the JSON should be valid according to this schema:
    """
    {
      "$schema": "http://json-schema.org/draft-04/schema#",
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "id": { "type": "number"},
          "path": { "type": "string"},
          "blob": { "type": "object"}
        },
      "additionalProperties": false
      }
    }
    """

    When I send a "GET" request to "api/v2/apps/~appId~/assets"
    Then the response status code should be 200
    And the JSON node "root" should have 3 elements

    Examples:
      | packageName       |
      | @deskproapps/jira |
      | simple-app        |

