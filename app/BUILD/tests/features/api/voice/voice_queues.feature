@new
Feature: /voice_queues endpoint

  Background:
    Given I'm authenticated as admin
    And the setting "beta_features.voice" is set to 1
    And I have only default brand
    And no VoiceQueue records exist
    And only the following Department records exist:
      | #  | Title               | Brands           | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Ticket Department 1 | [{defaultBrand}] | 1                  | 0               |
      | d2 | Ticket Department 2 | [{defaultBrand}] | 1                  | 0               |

  Scenario: I retrieve a list of voice queues
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |
      | q2 | Queue 2 | round_robin   |
      | q3 | Queue 3 | round_robin   |

    When I send a GET request to "/api/v2/voice_queues"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements

  Scenario: I get voice queue
    Given only the following VoiceQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |

    When I send a GET request to "/api/v2/voice_queues/{q1}"
    Then the response status code should be 200

  Scenario: I create a new voice queue
    Given "agent_1@example.com" admin exists
    And "agent_2@example.com" admin exists
    And "agent_3@example.com" admin exists
    When I send a POST request to "/api/v2/voice_queues" with body:
    """
{
  "department": ~d1~,
  "name": "My Queue",
  "routing_model": "least_utilized",
  "max_queue_size": 10,
  "agents": [{"agent": ~agent_1@example.com~, "is_enabled": true}, {"agent": ~agent_2@example.com~}, {"agent": ~agent_3@example.com~}],
  "voicemail_timeout": 30
}
    """
    Then the response status code should be 201
    And the JSON node "data.name" should be equal to the string "My Queue"
    And the JSON node "data.routing_model" should be equal to the string "least_utilized"
    And the JSON node "data.department" should be equal to "{d1}"
    And the JSON node "data.max_queue_size" should be equal to 10
    And the JSON node "data.agents" should have 3 elements
    And the JSON node "data.agents[0].agent" should be equal to "{agent_1@example.com}"
    And the JSON node "data.agents[0].is_enabled" should be equal to 1
    And the JSON node "data.agents[1].agent" should be equal to "{agent_2@example.com}"
    And the JSON node "data.agents[1].is_enabled" should be equal to 0
    And the JSON node "data.agents[2].agent" should be equal to "{agent_3@example.com}"
    And the JSON node "data.agents[2].is_enabled" should be equal to 0

  Scenario: I update voice queue
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

  Scenario: I delete voice queue
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
