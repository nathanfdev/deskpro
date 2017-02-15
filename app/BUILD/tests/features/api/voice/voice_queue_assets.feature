@new
Feature: Voice queue assets

  Background:
    Given I'm authenticated as admin
    And only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
    And only the following VoiceTextAsset records exist:
      | #   | Text       | Language | Auth               |
      | ta1 | text asset | en-GB    | AAAAAAAAAAAAAAAAAA |

  Scenario: I set greet asset
    When I send a PUT request to "/api/v2/voice_queues/{q1}" with body:
    """
{
  "greet_asset": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_queues/{q1}"
    Then the JSON node "data.greet_asset.auth" should be equal to the string "AAAAAAAAAAAAAAAAAA"
    And the JSON node "data.greet_asset.type" should be equal to the string "text"
    And the JSON node "data.greet_asset.text" should be equal to the string "text asset"
    And the JSON node "data.greet_asset.language" should be equal to the string "en-GB"

  Scenario: I set voicemail asset
    When I send a PUT request to "/api/v2/voice_queues/{q1}" with body:
    """
{
  "voicemail_asset": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_queues/{q1}"
    Then the JSON node "data.voicemail_asset.auth" should be equal to the string "AAAAAAAAAAAAAAAAAA"

  Scenario: I set loop asset
    When I send a PUT request to "/api/v2/voice_queues/{q1}" with body:
    """
{
  "loop_asset": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_queues/{q1}"
    Then the JSON node "data.loop_asset.auth" should be equal to the string "AAAAAAAAAAAAAAAAAA"

  Scenario: I set uploaded data
    When I send a PUT request to "/api/v2/voice_queues/{q1}" with body:
    """
{
  "loop_asset": {
    "id": ~ta1~,
    "auth": "AAAAAAAAAAAAAAAAAA",
    "text": "text asset",
    "language": "en-GB",
    "type": "text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_queues/{q1}"
    Then the JSON node "data.loop_asset.auth" should be equal to the string "AAAAAAAAAAAAAAAAAA"
