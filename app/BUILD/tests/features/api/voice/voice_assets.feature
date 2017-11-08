@new
Feature: /voice_assets endpoint

  Background:
    Given I'm authenticated as admin
    And no VoiceTextAsset records exist
    And no VoiceRecordAsset records exist
    And no VoiceUploadAsset records exist
    And there are no Blob records in the DB
    And the setting "beta_features.voice" is set to 1

  Scenario: I create text asset
    When I send a POST request to "/api/v2/voice_assets/create" with body:
    """
{
  "type": "text",
  "text": "my text",
  "language": "en-GB"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.type" should be equal to the string "text"
    And the JSON node "data.text" should be equal to the string "my text"
    And the JSON node "data.language" should be equal to the string "en-GB"
    And the JSON node "data.blob" should not exist
    And the JSON node "data.name" should not exist

  Scenario: I create record asset
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    When I send a POST request to "/api/v2/voice_assets/create" with body:
    """
{
  "type": "record",
  "name": "my record",
  "blob": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.type" should be equal to the string "record"
    And the JSON node "data.name" should be equal to the string "my record"
    And the JSON node "data.blob.blob_auth" should be equal to the string "AAAAAAAAAAAAAAAAAA"
    And the JSON node "data.text" should not exist
    And the JSON node "data.language" should not exist

  Scenario: I create upload asset
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    When I send a POST request to "/api/v2/voice_assets/create" with body:
    """
{
  "type": "upload",
  "blob": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.type" should be equal to the string "upload"
    And the JSON node "data.blob.blob_auth" should be equal to the string "AAAAAAAAAAAAAAAAAA"
    And the JSON node "data.text" should not exist
    And the JSON node "data.language" should not exist

  Scenario: Upload asset validation
    When I send a POST request to "/api/v2/voice_assets/create" with body:
    """
{
  "name": "",
  "type": "upload"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.blob.errors[0].code" should be equal to the string "required"

  Scenario: Record asset validation
    When I send a POST request to "/api/v2/voice_assets/create" with body:
    """
{
  "name": "",
  "type": "record"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.name.errors[0].code" should be equal to the string "required"
    And the JSON node "errors.fields.blob.errors[0].code" should be equal to the string "required"

  Scenario: Text asset validation
    When I send a POST request to "/api/v2/voice_assets/create" with body:
    """
{
  "type": "text"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.text.errors[0].code" should be equal to the string "required"
    And the JSON node "errors.fields.language.errors[0].code" should be equal to the string "required"
    And the JSON node "errors.fields.language.errors[1].code" should be equal to the string "bad_choice"
