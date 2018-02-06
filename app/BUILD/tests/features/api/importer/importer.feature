@new
Feature: /importer
  Check importer endpoints

  Background:
    Given I'm authenticated as admin
    And no Job records exist

  Scenario: No importer type validation
    When I send a POST request to "/api/v2/importer/start_import"
    Then the response status code should be 400
    And the JSON node "errors.fields.type.errors[0].code" should be equal to "required"

  Scenario: No active import jobs status
    When I send a GET request to "/api/v2/importer/status"
    Then the response status code should be 404
