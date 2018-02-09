@new @apps
Feature: install v2 apps
  As an admin user
  I want to install v2 apps

  Background:
    Given I'm authenticated as admin
    And there are no "App" records

  Scenario: I install an application via the api
    Given I package the app from folder "resources/apps/state-tests"
    When I send a POST request to "/api/v2/apps" with content type "application/zip" and file "{lastPackagedApp}" as body
    Then the response status code should be 200
    And I save the JSON node "id" as placeholder "instance"
    When I send a DELETE request to "/api/v2/apps/~instance~"
    Then the response status code should be 204

  Scenario: After uninstall, the custom fields are renamed
    And I package the app from folder "resources/apps/state-tests"
    And I send a POST request to "/api/v2/apps" with content type "application/zip" and file "{lastPackagedApp}" as body
    And I save the JSON node "id" as placeholder "instance"
    And I send a POST request to "/api/v2/ticket_custom_fields" with a json body:
    """
{
  "title":"aliased",
  "is_enabled":true,
  "alias": "app:~instance~:deskbro",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DateTime"
}
    """
    Then print last response body
    And the JSON node "data.title" should be equal to "aliased"
    And I save the JSON node "data.id" as placeholder "field"
    When I send a DELETE request to "/api/v2/apps/~instance~"
    And I send a GET request to "/api/v2/ticket_custom_fields/~field~"
    Then the JSON node "data.title" should be equal to "aliased (app removed)"
