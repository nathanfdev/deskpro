@new
Feature: /articles endpoint
  I want to check permission groups

  Background:
    Given no Person records exist
    And I'm authenticated as agent
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

  Scenario: I have no article permissions
    When I send a GET request to "/api/v2/articles"
    Then the response status code should be 403

  Scenario: I grant use articles permission
    Given I add "agent" usergroup relation "agent_all_perms"
    When I send a GET request to "/api/v2/articles"
    Then the response status code should be 200

  Scenario: Admin has all safe permissions
    Given I add "agent" usergroup relation "agent_all_safe_perms"
    When I send a GET request to "/api/v2/articles"
    Then the response status code should be 200
