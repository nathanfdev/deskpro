@new
Feature: /voice_queues endpoint

  Background:
    Given I'm authenticated as admin
    And the setting "beta_features.voice" is set to 1
    And only the following VoiceAccount records exist:
      | #  | AccountName | AccountSid | AuthToken |
      | a1 | Account 1   | Sid1       | Token1    |

  Scenario: I retrieve a list of twilio queues
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
      | q2 | Queue 2 | round_robin   |
      | q3 | Queue 3 | round_robin   |

    When I send a GET request to "/api/v2/voice_queues"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements

  Scenario: I get twilio queue
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |

    When I send a GET request to "/api/v2/voice_queues/{q1}"
    Then the response status code should be 200

  Scenario: I create a new twilio queue
    Given only the following User records exist:
      | #  | Name     |
      | p1 | Person 1 |
      | p2 | Person 2 |
      | p3 | Person 3 |
    When I send a POST request to "/api/v2/voice_queues" with body:
    """
{
  "account": ~a1~,
  "name": "My Queue",
  "routing_model": "least_utilized",
  "max_queue_size": 10,
  "agents": [{"agent": ~p1~, "is_enabled": true}, {"agent": ~p2~}, {"agent": ~p3~}],
  "voicemail_timeout": 30
}
    """
    Then the response status code should be 201
    And the JSON node "data.name" should be equal to the string "My Queue"
    And the JSON node "data.routing_model" should be equal to the string "least_utilized"
    And the JSON node "data.max_queue_size" should be equal to 10
    And the JSON node "data.agents" should have 3 elements
    And the JSON node "data.agents[0].agent" should be equal to "{p1}"
    And the JSON node "data.agents[0].is_enabled" should be equal to 1
    And the JSON node "data.agents[1].agent" should be equal to "{p2}"
    And the JSON node "data.agents[1].is_enabled" should be equal to 0
    And the JSON node "data.agents[2].agent" should be equal to "{p3}"
    And the JSON node "data.agents[2].is_enabled" should be equal to 0

  Scenario: I update twilio queue
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
    When I send a PUT request to "/api/v2/voice_queues/{q1}" with body:
    """
{
  "name": "Updated name"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_queues/{q1}"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to the string "Updated name"

  Scenario: I delete twilio queue
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
      | q2 | Queue 2 | round_robin   |
      | q3 | Queue 3 | round_robin   |
    When I send a DELETE request to "/api/v2/voice_queues/{q2}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/voice_queues"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

  Scenario: I validate unique queue name
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |

    When I send a POST request to "/api/v2/voice_queues" with body:
    """
{
  "name": "Queue 1"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.name.errors[0].code" should be equal to the string "unique_entity"
