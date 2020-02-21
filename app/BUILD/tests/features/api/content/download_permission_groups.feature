@new
Feature: /downloads endpoint

  Background:
    Given no Person records exist
    And there are no "Permission" records
    And I'm authenticated as agent
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

  Scenario: I have no download permissions
    When I send a GET request to "/api/v2/downloads"
    Then the response status code should be 403

  Scenario: Agent has all safe permissions
    Given I add "agent" usergroup relation "agent_all_safe_perms"
    When I send a GET request to "/api/v2/downloads"
    Then the response status code should be 200

  Scenario: Agent has all permissions
    Given I add "agent" usergroup relation "agent_all_perms"
    When I send a GET request to "/api/v2/downloads"
    Then the response status code should be 200
