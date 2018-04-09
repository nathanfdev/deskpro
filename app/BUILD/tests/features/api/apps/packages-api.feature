@new @apps
Feature: apps packages api
  As an apps API client
  I want to retrieve information about apps packages

  Background:
    Given I'm authenticated as admin
    And there are no "App" records
    # This will add a `lastCreatedInstanceId`, `lastInstalledAppId` context variable
    And I install the app from folder "resources/apps/state-tests"
    And I install the app from folder "resources/apps/simple-app"

  Scenario Outline: I request information about an application package
    Given I'm authenticated via session as agent
    When I send a "GET" request to "api/v2/apps/packages/<packageName>"
    Then the response status code should be 200
    Then the JSON node "data.name" should be equal to "<packageName>"

    Given I save the JSON node "data.id" as placeholder "appId"
    When I send a "GET" request to "api/v2/apps/packages/~appId~"
    Then the response status code should be 200
    Then the JSON node "data.name" should be equal to "<packageName>"

  Examples:
  | packageName       |
  | @deskproapps/jira |
  | simple-app        |

  Scenario Outline: I request information about an application manifest
    Given I'm authenticated via session as agent
    And I send a "GET" request to "api/v2/apps/packages/<packageName>"
    And I save the JSON node "data.id" as placeholder "appId"

    When I send a "GET" request to "api/v2/apps/packages/<name>/manifest"
    Then the response status code should be 200
    Then the JSON node "name" should be equal to "<packageName>"

    When I send a "GET" request to "api/v2/apps/packages/~appId~/manifest"
    Then the response status code should be 200
    Then the JSON node "name" should be equal to "<packageName>"

    Examples:
      | packageName       |
      | @deskproapps/jira |
      | simple-app        |

  Scenario Outline: I request information about the changes to an application's manifest
    Given I'm authenticated via session as agent
    And I send a "GET" request to "api/v2/apps/packages/<packageName>"
    And I save the JSON node "data.id" as placeholder "appId"
    When I send a "GET" request to "api/v2/apps/packages/<packageName>/manifest-changes"
    Then the response status code should be 200

    When I send a "GET" request to "api/v2/apps/packages/~appId~/manifest-changes"
    Then the response status code should be 200

    Examples:
      | packageName       |
      | @deskproapps/jira |
      | simple-app        |
