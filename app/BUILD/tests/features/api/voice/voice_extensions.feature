@new
Feature: Twilio extensions

  Background:
    Given no VoiceAutoAttendant records exist
    And no Person records exist
    And I'm authenticated as admin
    And the setting "beta_features.voice" is set to 1
    And only the following VoiceTextAsset records exist:
      | #   | Text       | Language | Auth               |
      | ta1 | text asset | en-GB    | AAAAAAAAAAAAAAAAAA |

  Scenario: I get agent data
    Given only the following VoiceTextAsset records exist:
      | #   | Text       | Language |
      | ta1 | text asset | en-GB    |
    And only the following AgentData records exist:
      | #  | Extension Number | Voicemail Asset |
      | a1 | 1001             | {ta1}           |
    And the "{admin}" record "agent_data" prop is equal to "{a1}"

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.agent_data.extension_number" should be equal to 1001
    And the JSON node "data.agent_data.voicemail_asset.text" should be equal to the string "text asset"

  Scenario: I update agent data
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "agent_data": {
    "extension_number": 1001,
    "voicemail_asset": "AAAAAAAAAAAAAAAAAA"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.agent_data.extension_number" should be equal to 1001
    And the JSON node "data.agent_data.voicemail_asset.text" should be equal to the string "text asset"

  Scenario: Extension number min range validation
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "agent_data": {
    "extension_number": 1000
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.agent_data.fields.extension_number.errors[0].code" should be equal to the string "too_low"

  Scenario: Extension number max range validation
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "agent_data": {
    "extension_number": 10000
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.agent_data.fields.extension_number.errors[0].code" should be equal to the string "too_high"

  Scenario: Unique extension number validation
    And only the following AgentData records exist:
      | #  | Person  | Extension Number |
      | a1 | {admin} | 1001             |
    And "agent_1@deskpro.dev" agent exists

    When I send a PUT request to "/api/v2/people/{agent_1@deskpro.dev}" with body:
    """
{
  "agent_data": {
    "extension_number": 1001
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.agent_data.fields.extension_number.errors[0].code" should be equal to the string "unique_entity"

  Scenario: Delete extension number
    And only the following AgentData records exist:
      | #  | Person  | Extension Number |
      | a1 | {admin} | 1001             |

    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "agent_data": {
    "extension_number": null
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.agent_data.extension_number" should be null
