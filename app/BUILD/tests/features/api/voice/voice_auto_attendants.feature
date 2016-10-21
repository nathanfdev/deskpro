@new
Feature: /voice_auto_attendants endpoint

  Background:
    Given no VoiceAutoAttendant records exist
    And I'm authenticated as admin

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
    And the JSON node "data.dial_numbers" should have 0 elements
    And the JSON node "data.allow_repeat_menu" should be equal to 1
    And the JSON node "data.allow_extension" should be equal to 1

  Scenario: I set audio asset
    Given only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |

    When I send a PUT request to "/api/v2/voice_auto_attendants/{a1}" with body:
    """
{
  "audio_asset": {
    "name": "My asset",
    "type": "text",
    "text": "my text",
    "language": "en-GB"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_auto_attendants/{a1}"
    Then the response status code should be 200
    And the JSON node "data.audio_asset.name" should be equal to the string "My asset"

  Scenario: I set targets
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
    And only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |
      | a2 | Auto attendant 2 |

    When I send a PUT request to "/api/v2/voice_auto_attendants/{a1}" with body:
    """
{
  "dial_numbers": [
    {
      "dial_num": 1,
      "target": {
        "type": "queue",
        "queue": ~q1~
      }
    },
    {
      "dial_num": 2,
      "target": {
        "type": "agent",
        "agent": ~admin~
      }
    },
    {
      "dial_num": 5,
      "target": {
        "type": "auto_attendant",
        "auto_attendant": ~a2~
      }
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_auto_attendants/{a1}"
    Then the response status code should be 200
    And the JSON node "data.dial_numbers" should have 3 elements
    And the JSON node "data.dial_numbers[0].target.type" should be equal to the string "queue"
    And the JSON node "data.dial_numbers[0].target.queue" should be equal to "{q1}"
    And the JSON node "data.dial_numbers[1].target.type" should be equal to the string "agent"
    And the JSON node "data.dial_numbers[1].target.agent" should be equal to "{admin}"
    And the JSON node "data.dial_numbers[2].target.type" should be equal to the string "auto_attendant"
    And the JSON node "data.dial_numbers[2].target.auto_attendant" should be equal to "{a2}"

  Scenario: I delete auto attendant
    Given only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |

    When I send a DELETE request to "/api/v2/voice_auto_attendants/{a1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/voice_auto_attendants"
    And the JSON node "data" should have 0 elements

  Scenario: dial number unique validation
    Given only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |

    When I send a PUT request to "/api/v2/voice_auto_attendants/{a1}" with body:
    """
{
  "dial_numbers": [
    {
      "dial_num": 1,
      "target": {
        "type": "agent",
        "agent": ~admin~
      }
    },
    {
      "dial_num": 1,
      "target": {
        "type": "agent",
        "agent": ~admin~
      }
    }
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.dial_numbers.errors[0].code" should be equal to the string "not_unique_collection"

  Scenario: dial number range validation
    Given only the following VoiceAutoAttendant records exist:
      | #  | Name             |
      | a1 | Auto attendant 1 |

    When I send a PUT request to "/api/v2/voice_auto_attendants/{a1}" with body:
    """
{
  "dial_numbers": [
    {
      "dial_num": 10,
      "target": {
        "type": "agent",
        "agent": ~admin~
      }
    }
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.dial_numbers.fields.dial_numbers_0.fields.dial_num.errors[0].code" should be equal to the string "too_high"

  Scenario: I set audio asset by id
    Given only the following VoiceAutoAttendant records exist:
      | #              | Name             |
      | auto_attendant | Auto attendant 1 |
    And only the following VoiceAsset records exist:
      | #     | Name     | Type | Text    | Language |
      | asset | My asset | text | my text | en-GB    |

    When I send a PUT request to "/api/v2/voice_auto_attendants/{auto_attendant}" with body:
    """
{
  "audio_asset": ~asset~
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_auto_attendants/{auto_attendant}"
    Then the response status code should be 200
    And the JSON node "data.audio_asset.name" should be equal to the string "My asset"
