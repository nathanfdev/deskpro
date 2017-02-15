@new
Feature: Voice assets

  Background:
    Given I'm authenticated as admin
    And only the following VoiceTextAsset records exist:
      | #   | Text               | Language | Auth               |
      | ta1 | text asset         | en-GB    | AAAAAAAAAAAAAAAAAA |
      | ta2 | another text asset | en-GB    | BBBBBBBBBBBBBBBBBB |

  Scenario: I create text asset
    When I send a PUT request to "/api/v2/agents/profile" with body:
    """
{
  "agent_data": {
    "voicemail_asset": "AAAAAAAAAAAAAAAAAA"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/me"
    Then the JSON node "data.person.agent_data.voicemail_asset.id" should exist
    And the JSON node "data.person.agent_data.voicemail_asset.type" should be equal to the string "text"
    And the JSON node "data.person.agent_data.voicemail_asset.text" should be equal to the string "text asset"
    And the JSON node "data.person.agent_data.voicemail_asset.language" should be equal to the string "en-GB"
    And the JSON node "data.person.agent_data.voicemail_asset.name" should not exist
    And the JSON node "data.person.agent_data.voicemail_asset.blob" should not exist

  Scenario: I change asset type
    Given only the following AgentData records exist:
      | #  | Person  | Voicemail Asset |
      | d1 | {admin} | {ta1}           |

    When I send a PUT request to "/api/v2/agents/profile" with body:
    """
{
  "agent_data": {
    "voicemail_asset": "BBBBBBBBBBBBBBBBBB"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/me"
    Then the response status code should be 200
    And the JSON node "data.person.agent_data.voicemail_asset.text" should be equal to the string "another text asset"
