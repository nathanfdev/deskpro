@new
Feature: /ticket_forms validation
  I want to check labels validation

  Background:
    Given I'm authenticated as admin

  Scenario: I sent not valid data in labels
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "labels": [
    {"id": 1}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.labels.errors" should not exist
    And the JSON node "errors.fields.labels.fields" should have 1 element
    And the JSON node "errors.fields.labels.fields.labels_0.errors" should have 1 element
    And the JSON node "errors.fields.labels.fields.labels_0.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I sent empty label
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "labels": ["", "label1"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.labels.errors" should not exist
    And the JSON node "errors.fields.labels.fields" should have 1 element
    And the JSON node "errors.fields.labels.fields.labels_0.errors" should have 1 element
    And the JSON node "errors.fields.labels.fields.labels_0.errors[0].code" should be equal to "required"

  Scenario: I sent not unique collection
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "labels": ["label1", "label1"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.labels.errors" should have 1 element
    And the JSON node "errors.fields.labels.errors[0].code" should be equal to "not_unique_collection"
    And the JSON node "errors.fields.labels.fields" should not exist
