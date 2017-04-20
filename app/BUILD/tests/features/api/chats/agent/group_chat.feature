@new
Feature: /agent_chats endpoint
  To check chat with group

  Background:
    Given I'm authenticated as "agent"
    And the setting "beta_features.agent_chat" is set to 1

  Scenario: I create chat with group
    Given an agent with "han@falcon.spaceship" email exists
    And an agent with "chewie@falcon.spaceship" email exists
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "group",
  "participant": [~han@falcon.spaceship~, ~chewie@falcon.spaceship~],
  "name": "Falcon crew and I"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/agent_chats/{lastCreatedId}"
    And the JSON node "data.agents" should exist
    And the JSON node "data.agents" should have 3 element
    And the JSON node "data.agents[0]" should be equal to "{han@falcon.spaceship}"
    And the JSON node "data.agents[1]" should be equal to "{chewie@falcon.spaceship}"
    And the JSON node "data.agents[2]" should be equal to "{agent}"

  Scenario: I create chat with group and fail validation
    Given an agent with "jabba.hutt@tatooine.planet" email exists
    And an agent with "boba.fett@bounty.hunters" email exists
    When I send a POST request to "api/v2/agent_chats" with body:
    """
{
  "type": "group",
  "participant": [~jabba.hutt@tatooine.planet~, ~boba.fett@bounty.hunters~]
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "code" should be equal to "invalid_input"
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I get a group chat
    Given an agent with "luke.skywalker@jedi.order" email exists
    And an agent with "leia.organa@alderaan.planet" email exists
    And the following "AgentChat" records exist:
      | #                | Type  | Name                    | Admin                 |
      | agent-group-chat | group | Falcon passengers And I | ~luke.skywalker@jedi.order~ |
    And the following "AgentChatParticipant" records exist:
      | Chat              | Person |
      | {agent-group-chat} | {agent} |
      | {agent-group-chat} | {leia.organa@alderaan.planet} |
      | {agent-group-chat} | {luke.skywalker@jedi.order} |
    When I send a GET request to "/api/v2/agent_chats/{agent-group-chat}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.chat_type" should be equal to "group"
    And the JSON node "data.agents" should have 3 element
    And the JSON node "data.agents[0]" should be equal to "{agent}"
    And the JSON node "data.agents[1]" should be equal to "{leia.organa@alderaan.planet}"
    And the JSON node "data.agents[2]" should be equal to "{luke.skywalker@jedi.order}"

  Scenario: I get group chats list
    Given an agent with "luke.skywalker@jedi.order" email exists
    And an agent with "leia.organa@alderaan.planet" email exists
    And an agent with "han@falcon.spaceship" email exists
    And an agent with "chewie@falcon.spaceship" email exists
    And only the following "AgentChat" records exist:
      | #          | Type  | Name       | Admin                       |
      | crew       | group | Crew       | ~han@falcon.spaceship~      |
      | passengers | group | Passengers | ~luke.skywalker@jedi.order~ |
      | dwellers   | group | Dwellers   | ~agent~                     |
    And the following "AgentChatParticipant" records exist:
      | Chat         | Person                        |
      | {crew}       | {han@falcon.spaceship}        |
      | {crew}       | {chewie@falcon.spaceship}     |
      | {passengers} | ~luke.skywalker@jedi.order~   |
      | {passengers} | ~leia.organa@alderaan.planet~ |
      | {passengers} | ~agent~                       |
      | {dwellers}   | {han@falcon.spaceship}        |
      | {dwellers}   | {chewie@falcon.spaceship}     |
      | {dwellers}   | ~luke.skywalker@jedi.order~   |
      | {dwellers}   | ~leia.organa@alderaan.planet~ |
      | {dwellers}   | ~agent~                       |
    When I send a GET request to "/api/v2/agent_chats/groups"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data" should have 2 element

    And the JSON node "data[0].chat_type" should be equal to "group"
    And the JSON node "data[0].agents[0]" should be equal to "{luke.skywalker@jedi.order}"
    And the JSON node "data[0].agents[1]" should be equal to "{leia.organa@alderaan.planet}"
    And the JSON node "data[0].agents[2]" should be equal to "{agent}"
    And the JSON node "data[0].name" should be equal to "Passengers"
    And the JSON node "data[1].chat_type" should be equal to "group"
    And the JSON node "data[1].agents" should have 5 elements
    And the JSON node "data[1].name" should be equal to "Dwellers"