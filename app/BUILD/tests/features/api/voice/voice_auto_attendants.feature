@new
Feature: /voice_auto_attendants endpoint

  Background:
    Given no VoiceAutoAttendant records exist
    And I'm authenticated as admin
    And the setting "beta_features.voice" is set to 1

  Scenario: I retrieve a list of voice auto attendants
    Given only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |
      | a2 | Auto attendant 2 |
      | a3 | Auto attendant 3 |

    When I send a GET request to "/api/v2/voice_auto_attendants"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements

  Scenario: I create a new auto attendant
    When I send a POST request to "/api/v2/voice_auto_attendants" with body:
    """
{
  "name": "New auto attendant",
  "allow_repeat_menu": true,
  "allow_extension": true
}
    """
    Then the response status code should be 201
    And the JSON node "data.name" should be equal to the string "New auto attendant"
    And the JSON node "data.audio_asset" should be null
    And the JSON node "data.targets" should have 9 elements
    And the JSON node "data.targets.1" should be null
    And the JSON node "data.targets.2" should be null
    And the JSON node "data.targets.3" should be null
    And the JSON node "data.targets.4" should be null
    And the JSON node "data.targets.5" should be null
    And the JSON node "data.targets.6" should be null
    And the JSON node "data.targets.7" should be null
    And the JSON node "data.targets.8" should be null
    And the JSON node "data.targets.9" should be null
    And the JSON node "data.allow_repeat_menu" should be equal to 1
    And the JSON node "data.allow_extension" should be equal to 1

  Scenario: I set audio asset
    Given only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |
    And only the following VoiceTextAsset records exist:
      | #   | Text       | Language | Auth               |
      | ta1 | text asset | en-GB    | AAAAAAAAAAAAAAAAAA |

    When I send a PUT request to "/api/v2/voice_auto_attendants/{a1}" with body:
    """
{
  "audio_asset": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_auto_attendants/{a1}"
    Then the response status code should be 200
    And the JSON node "data.audio_asset.text" should be equal to the string "text asset"

  Scenario: I set targets
    Given only the following TwilioVoiceAccount records exist:
      | #       | AccountName | AccountId | AuthToken |
      | account | Account 1   | Sid1      | Token1    |
    And only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
    And only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |
      | a2 | Auto attendant 2 |

    When I send a PUT request to "/api/v2/voice_auto_attendants/{a1}" with body:
    """
{
  "targets": {
    "1": {
      "type": "queue",
      "target": ~q1~
    },
    "2": {
      "type": "agent",
      "target": ~admin~
    },
    "5": {
      "type": "auto_attendant",
      "target": ~a2~
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_auto_attendants/{a1}"
    Then the response status code should be 200
    And the JSON node "data.targets" should have 9 elements
    And the JSON node "data.targets.1.type" should be equal to the string "queue"
    And the JSON node "data.targets.1.target" should be equal to "{q1}"
    And the JSON node "data.targets.2.type" should be equal to the string "agent"
    And the JSON node "data.targets.2.target" should be equal to "{admin}"
    And the JSON node "data.targets.5.type" should be equal to the string "auto_attendant"
    And the JSON node "data.targets.5.target" should be equal to "{a2}"
    And the JSON node "data.targets.3" should be null
    And the JSON node "data.targets.4" should be null
    And the JSON node "data.targets.6" should be null
    And the JSON node "data.targets.8" should be null
    And the JSON node "data.targets.9" should be null

  Scenario: I delete auto attendant
    Given only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |

    When I send a DELETE request to "/api/v2/voice_auto_attendants/{a1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/voice_auto_attendants"
    And the JSON node "data" should have 0 elements

  Scenario: dial number range validation
    Given only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |

    When I send a PUT request to "/api/v2/voice_auto_attendants/{a1}" with body:
    """
{
  "targets": {
    "10": {
      "type": "agent",
      "agent": ~admin~
    }
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.targets.errors[0].code" should be equal to the string "extra_fields"
