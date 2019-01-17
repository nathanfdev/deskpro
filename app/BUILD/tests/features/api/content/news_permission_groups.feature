@new
Feature: /news endpoint

  Scenario: I have no news permissions
    Given I'm authenticated as agent
    And there are no "Permission" records
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 403

  Scenario: I grant use news permission
    Given I'm authenticated as agent
    And I set permission "news.use" = 1 for "registered" usergroup
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 200

  Scenario: Agent has all safe permissions
    Given I'm authenticated as admin
    And I remove "agent" usergroup relation "agent_all_perms"
    And I add "agent" usergroup relation "agent_all_safe_perms"
    And I set permission "news.use" = 0 for "registered" usergroup
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 200

  Scenario: Agent has all permissions
    Given I'm authenticated as admin
    And I add "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    And I set permission "news.use" = 0 for "registered" usergroup
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 200
