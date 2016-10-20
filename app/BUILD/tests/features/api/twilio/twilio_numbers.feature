@new
Feature: /voice_numbers endpoint

  Background:
    Given I'm authenticated as admin
    And only the following VoiceAccount records exist:
      | #  | AccountName | AccountSid | AuthToken |
      | a1 | Account 1   | Sid1       | Token1    |

  Scenario: I retrieve a list of twilio numbers
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Country Code |
      | n1 | Number 1 | sid1 | 111111 | gb           |
      | n2 | Number 2 | sid2 | 222222 | us           |
      | n3 | Number 3 | sid3 | 333333 | fr           |

    When I send a GET request to "/api/v2/voice_numbers"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements

  Scenario: I get twilio number
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Country Code |
      | n1 | Number 1 | sid1 | 111111 | gb           |

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
  "country_code": "gb"
}
    """
    Then the response status code should be 201
    And the JSON node "data.account" should be equal to "{a1}"
    And the JSON node "data.sid" should be equal to the string "sidcode"
    And the JSON node "data.nickname" should be equal to the string "nickname"
    And the JSON node "data.number" should be equal to the string "12345"
    And the JSON node "data.country_code" should be equal to the string "gb"
    And the JSON node "data.target_id" should be null
    And the JSON node "data.target_type" should be null

  Scenario: I update twilio number
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Country Code |
      | n1 | Number 1 | sid1 | 111111 | gb           |
    And only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
      | q2 | Queue 2 | round_robin   |
    When I send a PUT request to "/api/v2/voice_numbers/{n1}" with body:
    """
{
  "nickname": "new nickname",
  "target_id": ~q2~,
  "target_type": "queue"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200
    And the JSON node "data.target_type" should be equal to the string "queue"
    And the JSON node "data.target_id" should be equal to "{q2}"

  Scenario: I set target agent
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Country Code |
      | n1 | Number 1 | sid1 | 111111 | gb           |

    When I send a PUT request to "/api/v2/voice_numbers/{n1}" with body:
    """
{
  "nickname": "new nickname",
  "target_id": ~admin~,
  "target_type": "agent"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200
    And the JSON node "data.target_type" should be equal to the string "agent"
    And the JSON node "data.target_id" should be equal to "{admin}"

  Scenario: I delete twilio number
    Given only the following VoiceNumber records exist:
      | #  | Nickname | Sid  | Number | Country Code |
      | n1 | Number 1 | sid1 | 111111 | gb           |
      | n2 | Number 2 | sid2 | 222222 | us           |

    When I send a DELETE request to "/api/v2/voice_numbers/{n1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/voice_numbers"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
