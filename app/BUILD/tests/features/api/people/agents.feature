@new
Feature: /agents endpoint
  To retrieve DeskPRO agents
  As an API user
  I want an API endpoint

  Background:
    Given no Person records exist

  Scenario: I get list of all agents
    Given I'm authenticated as "agent"
    And I've just created a new agent with name "Alfred Zero"
    When I send a GET request to "/api/v2/agents"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].name" should be equal to "Alfred Zero"

  Scenario: I can fetch agents list even if I have no 'people.use' permission
    Given I'm authenticated as "agent"
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    And I've just created a new agent with name "Alfred Zero"
    When I send a GET request to "/api/v2/agents"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].name" should be equal to "Alfred Zero"

  Scenario: I soft-delete an agent and check agents list without having appropriate permissions
    Given I'm authenticated as "agent"
    And "borntobekilled@deskpro.dev" agent exists
    When I send a DELETE request to "/api/v2/people/{borntobekilled@deskpro.dev}"
    Then the response should be in JSON
    And the response status code should be 403

  Scenario: I soft-delete an agent and check agents list as admin
    Given I'm authenticated as "admin"
    And "borntobekilled@deskpro.dev" agent exists
    When I send a DELETE request to "/api/v2/agents/{borntobekilled@deskpro.dev}"
    Then print last JSON response
    And the response status code should be 200
    When I send a GET request to "/api/v2/agents"
    Then the response should not contain "borntobekilled@deskpro.dev"

  Scenario: I soft-delete an agent and check agents list as agent
    Given I'm authenticated as "agent"
    And I add "agent" usergroup relation "agent_all_perms"
    And "willbedeleted@deskpro.dev" agent exists
    When I send a DELETE request to "/api/v2/agents/{willbedeleted@deskpro.dev}"
    And I send a GET request to "/api/v2/agents"
    Then the response should not contain "willbedeleted@deskpro.dev"

  Scenario: I soft-delete an agent and check list of deleted agents
    Given I'm authenticated as "admin"
    And "tobedeleted@deskpro.dev" agent exists
    And "nottobedeletd@deskpro.dev" agent exists
    When I send a DELETE request to "/api/v2/agents/{tobedeleted@deskpro.dev}"
    And I send a GET request to "/api/v2/agents?is_deleted=1"
    Then the response should contain "tobedeleted@deskpro.dev"
    And the response should not contain "nottobedeletd@deskpro.dev"

  Scenario: I check combined list of soft-deleted and existing agents
    Given I'm authenticated as "admin"
    And "anotheragent@deskpro.dev" agent exists
    And "onemoreagent@deskpro.dev" agent exists
    When I send a DELETE request to "/api/v2/agents/{anotheragent@deskpro.dev}"
    And I send a GET request to "/api/v2/agents?is_deleted=-1"
    Then the response should contain "onemoreagent@deskpro.dev"
    And the response should contain "anotheragent@deskpro.dev"

  Scenario: I try to remove an agent via people endpoint
    Given I'm authenticated as "admin"
    And "yagni@deskpro.dev" agent exists
    When I send a DELETE request to "/api/v2/people/{yagni@deskpro.dev}"
    Then the response status code should be 400

  Scenario: I convert an agent to person and check agents list
    Given I'm authenticated as "admin"
    And "guineapig@deskpro.dev" agent exists
    When I send a DELETE request to "/api/v2/agents/{guineapig@deskpro.dev}/agent_permissions"
    Then the response status code should be 204
    When I send a GET request to "/api/v2/agents?is_deleted=-1"
    Then the response should not contain "guineapig@deskpro.dev"

  Scenario: I convert an agent to person and check agent's tickets are unassigned
    Given I'm authenticated as "admin"
    And I create an agent with 2 tickets
    When I send a DELETE request to the just created agent permissions resource
    And the response status code should be 204
    Then agent's ticket #1 should be unassigned
    And agent's ticket #2 should be unassigned

  Scenario: I convert an agent to person and check new person resource
    Given I'm authenticated as "admin"
    And I've just created a new agent with name "Alfred Seven"
    When I send a DELETE request to the just created agent permissions resource
    And I send a GET request to the last created agent resource via people endpoint
    Then the response status code should be 200

  Scenario: I create a person and try to access the new resource via agents endpoint
    Given I'm authenticated as "admin"
    And I've just created a new person with name "Alfred Eight"
    When I send a GET request to the last created person resource via agents endpoint
    Then the response status code should be 404

  Scenario: I give agent permissions to an existing user
    Given I'm authenticated as "admin"
    And I've just created a new person with name "Alfred Nine"
    When I send a PUT request to the last created person permissions resource:
    """
{
  "agent": true
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/agents"
    Then the response status code should be 200
    And the response should contain "Alfred Nine"
