@new
Feature: /importer
  Check Kayako importer

  Background:
    Given I'm authenticated as admin
    And no Job records exist

  Scenario: Kayako importer form validation
    When I send a POST request to "/api/v2/importer/start_import" with body:
    """
{
  "type": "kayako"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.options.fields.dbinfo.fields.dbname.errors[0].code" should be equal to "required"

  Scenario: Start Kayako import with required params
    When I send a POST request to "/api/v2/importer/start_import" with body:
    """
{
  "type": "kayako",
  "options": {
    "dbinfo": {
      "dbname": "kayako"
    }
  }
}
    """
    Then the response status code should be 200
    And the JSON node "data.id" should exist
    And the JSON node "data.source_type" should be equal to "kayako"
    And the JSON node "data.status" should be equal to "waiting"
    And the JSON node "data.imported_steps" should exist
    And the JSON node "data.imported_counts" should exist
    And the JSON node "data.applied_steps" should exist
    And the JSON node "data.applied_counts" should exist
    And the JSON node "data.date_created" should exist
    And the JSON node "data.log" should exist

  Scenario: Start Kayako import with all params
    When I send a POST request to "/api/v2/importer/start_import" with body:
    """
{
  "type": "kayako",
  "options": {
    "dbinfo": {
      "host": "localhost",
      "port": 3306,
      "user": "root",
      "password": "",
      "dbname": "kayako"
    }
  }
}
    """
    Then the response status code should be 200
    And the JSON node "data.id" should exist
    And the JSON node "data.source_type" should be equal to "kayako"
    And the JSON node "data.status" should be equal to "waiting"
