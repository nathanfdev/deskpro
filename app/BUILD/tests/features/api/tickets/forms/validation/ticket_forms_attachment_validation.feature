@new
Feature: /ticket_forms validation
  I want to check attachment validation

  Background:
    Given I'm authenticated as admin
    And the only default ticket layout exists with fields:
      | agent_layout |
      | attachments  |

  Scenario: I sent not valid attachments data
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "attachments": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.attachments.errors" should have 1 element
    And the JSON node "errors.fields.attachments.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.attachments.fields.attachments_0" should not exist

  Scenario: I sent not valid attachment
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "attachments": [
    "AAAAAAAAAAAAAAAAAA"
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.attachments.errors" should not exist
    And the JSON node "errors.fields.attachments.fields.attachments_0.errors" should have 1 element
    And the JSON node "errors.fields.attachments.fields.attachments_0.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I sent attachment with empty blob auth code
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "attachments": [
    {"blob_auth": ""}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.attachments.fields.attachments_0.errors" should have 1 element
    And the JSON node "errors.fields.attachments.fields.attachments_0.errors[0].code" should be equal to the string "no_uploaded_file"
    And the JSON node "errors.fields.attachments.fields.attachments_0.fields.blob_auth.errors" should have 1 element
    And the JSON node "errors.fields.attachments.fields.attachments_0.fields.blob_auth.errors[0].code" should be equal to "required"

  Scenario: I check that blob was not found
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "attachments": [
    {"blob_auth": "unknown_blob_auth"}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.attachments.errors" should not exist
    And the JSON node "errors.fields.attachments.fields.attachments_0.errors" should have 1 element
    And the JSON node "errors.fields.attachments.fields.attachments_0.errors[0].code" should be equal to "no_uploaded_file"
    And the JSON node "errors.fields.attachments.fields.attachments_0.fields.blob_auth.errors" should not exist
