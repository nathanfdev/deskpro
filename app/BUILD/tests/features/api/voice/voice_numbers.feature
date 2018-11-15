@new
Feature: /voice_numbers endpoint

  Background:
    Given I'm authenticated as admin
    And no VoiceNumber records exist
    And no VoiceQueueTarget records exist
    And no VoiceAgentTarget records exist
    And the setting "beta_features.voice" is set to 1
    And only the following TwilioVoiceAccount records exist:
      | #  | AccountName | AccountId | AuthToken |
      | a1 | Account 1   | Sid1      | Token1    |

  Scenario: I retrieve a list of twilio numbers
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number |
      | n1 | Number 1 | sid1 | 111111 |
      | n2 | Number 2 | sid2 | 222222 |
      | n3 | Number 3 | sid3 | 333333 |

    When I send a GET request to "/api/v2/voice_numbers"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements

  Scenario: I get twilio number
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number |
      | n1 | Number 1 | sid1 | 111111 |

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{n1}"

  Scenario: I add twilio number
    When I send a POST request to "/api/v2/voice_numbers" with body:
    """
{
  "account": ~a1~,
  "sid": "sidcode",
  "nickname": "nickname",
  "number": "12345",
  "target": {
    "type": "agent",
    "target": ~admin~
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.account" should be equal to "{a1}"
    And the JSON node "data.sid" should be equal to the string "sidcode"
    And the JSON node "data.nickname" should be equal to the string "nickname"
    And the JSON node "data.number" should be equal to the string "12345"
    And the JSON node "data.target.type" should be equal to the string "agent"
    And the JSON node "data.target.target" should be equal to "{admin}"

  Scenario: I set target queue
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number |
      | n1 | Number 1 | sid1 | 111111 |
    And only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
      | q2 | Queue 2 | round_robin   |
    When I send a PUT request to "/api/v2/voice_numbers/{n1}" with body:
    """
{
  "target": {
    "type": "queue",
    "target": ~q2~
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200
    And the JSON node "data.target.type" should be equal to the string "queue"
    And the JSON node "data.target.target" should be equal to "{q2}"

  Scenario: I set target agent
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number |
      | n1 | Number 1 | sid1 | 111111 |

    When I send a PUT request to "/api/v2/voice_numbers/{n1}" with body:
    """
{
  "nickname": "new nickname",
  "target": {
    "type": "agent",
    "target": ~admin~
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200
    And the JSON node "data.target.type" should be equal to the string "agent"
    And the JSON node "data.target.target" should be equal to "{admin}"

  Scenario: I change target type
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
    And only the following VoiceQueueTarget records exist:
      | #   | Queue |
      | qt1 | {q1}  |
    And only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Target |
      | n1 | Number 1 | sid1 | 111111 | {qt1}  |

    When I send a PUT request to "/api/v2/voice_numbers/{n1}" with body:
    """
{
  "nickname": "new nickname",
  "target": {
    "type": "agent",
    "target": ~admin~
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200
    And the JSON node "data.target.type" should be equal to the string "agent"
    And the JSON node "data.target.target" should be equal to "{admin}"

  Scenario: I delete target
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
    And only the following VoiceQueueTarget records exist:
      | #   | Queue |
      | qt1 | {q1}  |
    And only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Target |
      | n1 | Number 1 | sid1 | 111111 | {qt1}  |

    When I send a PUT request to "/api/v2/voice_numbers/{n1}" with body:
    """
{
  "nickname": "new nickname",
  "target": null
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.target.errors[0].code" should be equal to "required"

  Scenario: I delete twilio number
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number |
      | n1 | Number 1 | sid1 | 111111 |
      | n2 | Number 2 | sid2 | 222222 |

    When I send a DELETE request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/voice_numbers"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements

  Scenario: I set default countries for outgoing calls
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number |
      | n1 | Number 1 | sid1 | 111111 |
    When I send a PUT request to "/api/v2/voice_numbers/{n1}" with body:
    """
{
  "outbound_calls_default": true,
  "outbound_calls_default_type": "specific",
  "outbound_calls_default_countries": ["us", "GB"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200
    And the JSON node "data.outbound_calls_default" should be equal to 1
    And the JSON node "data.outbound_calls_default_type" should be equal to "specific"
    And the JSON node "data.outbound_calls_default_countries" should have 2 elements
    And the JSON node "data.outbound_calls_default_countries[0]" should be equal to "us"
    And the JSON node "data.outbound_calls_default_countries[1]" should be equal to "gb"

  Scenario: I set number as default for all outgoing calls
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number |
      | n1 | Number 1 | sid1 | 111111 |
    When I send a PUT request to "/api/v2/voice_numbers/{n1}" with body:
    """
{
  "outbound_calls_default": true,
  "outbound_calls_default_type": "all"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200
    And the JSON node "data.outbound_calls_default" should be equal to 1
    And the JSON node "data.outbound_calls_default_type" should be equal to "all"
    And the JSON node "data.outbound_calls_default_countries" should have 0 elements

  Scenario: I overwrite default country codes
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Outbound Calls Default Countries |
      | n1 | Number 1 | sid1 | 111111 | ["us", "uk", "fr"]               |
      | n2 | Number 2 | sid2 | 222222 | []                               |
      | n3 | Number 3 | sid3 | 333333 | ["ca", "it"]                     |

    When I send a PUT request to "/api/v2/voice_numbers/{n2}" with body:
    """
{
  "outbound_calls_default": true,
  "outbound_calls_default_countries": ["us", "gb", "ca"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n2}"
    Then the response status code should be 200
    And the JSON node "data.outbound_calls_default" should be equal to 1
    And the JSON node "data.outbound_calls_default_countries" should have 3 elements
    And the JSON node "data.outbound_calls_default_countries[0]" should be equal to "us"
    And the JSON node "data.outbound_calls_default_countries[1]" should be equal to "gb"
    And the JSON node "data.outbound_calls_default_countries[2]" should be equal to "ca"

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the JSON node "data.outbound_calls_default_countries" should have 2 elements
    And the JSON node "data.outbound_calls_default_countries[0]" should be equal to "uk"
    And the JSON node "data.outbound_calls_default_countries[1]" should be equal to "fr"

    When I send a GET request to "/api/v2/voice_numbers/{n3}"
    Then the JSON node "data.outbound_calls_default_countries" should have 1 element
    And the JSON node "data.outbound_calls_default_countries[0]" should be equal to "it"

  Scenario: I overwrite default number option
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Outbound Calls Default | Outbound Calls Default Type |
      | n1 | Number 1 | sid1 | 111111 | 1                      | all                         |
      | n2 | Number 2 | sid2 | 222222 | 1                      | country                     |
      | n3 | Number 3 | sid3 | 333333 | 1                      | specific                    |

    When I send a PUT request to "/api/v2/voice_numbers/{n2}" with body:
    """
{
  "outbound_calls_default": true,
  "outbound_calls_default_type": "all"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n2}"
    Then the JSON node "data.outbound_calls_default" should be equal to 1
    And the JSON node "data.outbound_calls_default_type" should be equal to "all"

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the JSON node "data.outbound_calls_default" should be equal to 1
    And the JSON node "data.outbound_calls_default_type" should be equal to "all"

    When I send a GET request to "/api/v2/voice_numbers/{n3}"
    Then the JSON node "data.outbound_calls_default" should be equal to 1
    And the JSON node "data.outbound_calls_default_type" should be equal to "specific"
