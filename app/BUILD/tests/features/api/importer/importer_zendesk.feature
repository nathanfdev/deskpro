@new
Feature: /importer
  Zendesk importer

  Background:
    Given I'm authenticated as admin
    And no Job records exist

  Scenario: Zendesk importer form validation
    When I send a POST request to "/api/v2/importer/start_import" with body:
    """
{
  "type": "zendesk"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.options.fields.account.fields.subdomain.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.options.fields.account.fields.username.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.options.fields.account.fields.token.errors[0].code" should be equal to "required"

  Scenario: Start Zendesk import
    When I send a POST request to "/api/v2/importer/start_import" with body:
    """
{
  "type": "zendesk",
  "options": {
    "account": {
      "subdomain": "subdomain",
      "username": "username@example.com",
      "token" : "token"
    }
  }
}
    """
    Then the response status code should be 200
    And the JSON node "data.id" should exist
    And the JSON node "data.source_type" should be equal to "zendesk"
    And the JSON node "data.status" should be equal to "waiting"
    And the JSON node "data.imported_steps" should exist
    And the JSON node "data.imported_counts" should exist
    And the JSON node "data.applied_steps" should exist
    And the JSON node "data.applied_counts" should exist
    And the JSON node "data.date_created" should exist
    And the JSON node "data.log" should exist
