@new
Feature: /user_chat_queues endpoint

  Background:
    Given I'm authenticated as admin
    And "agent1@deskpro.dev" agent exists
    And "agent2@deskpro.dev" agent exists
    And only the following AgentTeam records exist:
      | #  | Name   |
      | t1 | Team 1 |
      | t2 | Team 2 |
      | t3 | Team 3 |
    And only the following UserChatQueue records exist:
      | #  | Name    | Routing Model | Answer Timeout | Is All Agents |
      | q1 | Queue 1 | round_robin   | 10             | 1             |
      | q2 | Queue 2 | simulring     | 20             | 0             |
    And only the following UserChatQueueAgent records exist:
      | #   | Queue | Agent   | Sort |
      | qa1 | {q2}  | {admin} | 10   |
    And only the following UserChatQueueAgentTeam records exist:
      | #    | Queue | AgentTeam | Sort |
      | qat1 | {q2}  | {t1}      | 20   |
      | qat2 | {q2}  | {t2}      | 30   |

  Scenario: I retrieve a list of user chat queues
    When I send a GET request to "/api/v2/user_chat_queues?order_by=id&order_dir=asc"
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].name" should be equal to "Queue 1"
    And the JSON node "data[0].routing_model" should be equal to "round_robin"
    And the JSON node "data[0].answer_timeout" should be equal to 10
    And the JSON node "data[0].is_all_agents" should be equal to 1
    And the JSON node "data[0].targets" should have 0 elements

  Scenario: I get a user chat queue
    When I send a GET request to "/api/v2/user_chat_queues/{q2}"
    And the JSON node "data.name" should be equal to "Queue 2"
    And the JSON node "data.routing_model" should be equal to "simulring"
    And the JSON node "data.answer_timeout" should be equal to 20
    And the JSON node "data.is_all_agents" should be equal to 0
    And the JSON node "data.targets" should have 3 elements
    And the JSON node "data.targets[0].type" should be equal to "agent"
    And the JSON node "data.targets[0].target" should be equal to "{admin}"
    And the JSON node "data.targets[0].sort" should be equal to "10"
    And the JSON node "data.targets[1].type" should be equal to "agent_team"
    And the JSON node "data.targets[1].target" should be equal to "{t1}"
    And the JSON node "data.targets[1].sort" should be equal to "20"
    And the JSON node "data.targets[2].type" should be equal to "agent_team"
    And the JSON node "data.targets[2].target" should be equal to "{t2}"
    And the JSON node "data.targets[2].sort" should be equal to "30"

  Scenario: I create a user chat queue
    When I send a POST request to "/api/v2/user_chat_queues" with body:
    """
{
  "name": "My Queue",
  "routing_model": "least_utilized",
  "answer_timeout": 100,
  "is_all_agents": 0,
  "targets": [
    {"type": "agent", "target": ~agent1@deskpro.dev~, "sort": 10},
    {"type": "agent_team", "target": ~t1~, "sort": 20},
    {"type": "agent", "target": ~agent2@deskpro.dev~, "sort": 30},
    {"type": "agent_team", "target": ~t2~, "sort": 40}
  ]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/user_chat_queues/{lastCreatedId}"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "My Queue"
    And the JSON node "data.routing_model" should be equal to "least_utilized"
    And the JSON node "data.answer_timeout" should be equal to 100
    And the JSON node "data.targets" should have 4 elements
    And the JSON node "data.targets[0].type" should be equal to "agent"
    And the JSON node "data.targets[0].target" should be equal to "~agent1@deskpro.dev~"
    And the JSON node "data.targets[0].sort" should be equal to 10
    And the JSON node "data.targets[1].type" should be equal to "agent"
    And the JSON node "data.targets[1].target" should be equal to "~agent2@deskpro.dev~"
    And the JSON node "data.targets[1].sort" should be equal to 30
    And the JSON node "data.targets[2].type" should be equal to "agent_team"
    And the JSON node "data.targets[2].target" should be equal to "~t1~"
    And the JSON node "data.targets[2].sort" should be equal to 20
    And the JSON node "data.targets[3].type" should be equal to "agent_team"
    And the JSON node "data.targets[3].target" should be equal to "~t2~"
    And the JSON node "data.targets[3].sort" should be equal to 40

  Scenario: I update a user chat queue
    When I send a PUT request to "/api/v2/user_chat_queues/{q2}" with body:
    """
{
  "name": "Edited Queue",
  "routing_model": "least_utilized",
  "answer_timeout": 200,
  "is_all_agents": 1,
  "targets": [
    {"type": "agent", "target": ~agent1@deskpro.dev~, "sort": 10},
    {"type": "agent_team", "target": ~t1~, "sort": 20},
    {"type": "agent_team", "target": ~t2~, "sort": 30}
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/user_chat_queues/{q2}"
    And the JSON node "data.name" should be equal to "Edited Queue"
    And the JSON node "data.routing_model" should be equal to "least_utilized"
    And the JSON node "data.answer_timeout" should be equal to 200
    And the JSON node "data.is_all_agents" should be equal to 1
    And the JSON node "data.targets" should have 3 elements
    And the JSON node "data.targets[0].type" should be equal to "agent_team"
    And the JSON node "data.targets[0].target" should be equal to "~t1~"
    And the JSON node "data.targets[0].sort" should be equal to 20
    And the JSON node "data.targets[1].type" should be equal to "agent_team"
    And the JSON node "data.targets[1].target" should be equal to "~t2~"
    And the JSON node "data.targets[1].sort" should be equal to 30
    And the JSON node "data.targets[2].type" should be equal to "agent"
    And the JSON node "data.targets[2].target" should be equal to "~agent1@deskpro.dev~"
    And the JSON node "data.targets[2].sort" should be equal to 10

  Scenario: I delete a user chat queue
    When I send a DELETE request to "/api/v2/user_chat_queues/{q1}"
    Then the response status code should be 200

  Scenario: I check routing model validation
    When I send a POST request to "/api/v2/user_chat_queues" with body:
    """
{
  "name": "My Queue",
  "routing_model": "unknown"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.routing_model.errors[0].code" should be equal to "bad_choice"

  Scenario: I check agents validation
    Given "user1@deskpro.dev" user exists
    When I send a POST request to "/api/v2/user_chat_queues" with body:
    """
{
  "name": "My Queue",
  "targets": [
    {"type": "agent", "target": ~agent1@deskpro.dev~, "sort": 10},
    {"type": "agent", "target": ~user1@deskpro.dev~, "sort": 20}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.targets.fields.targets_1.fields.target.errors[0].code" should be equal to "bad_choice"
