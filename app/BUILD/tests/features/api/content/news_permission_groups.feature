Feature: /news endpoint
  I want to check permission groups

  Background:
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  Scenario: I have no news permissions
    Given I'm authenticated as agent
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 403

  Scenario: I grant use news permission
    Given I'm authenticated as agent
    And I set permission "news.use" = 1 for "registered" usergroup
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given I'm authenticated as admin
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"
    When I send a GET request to "/api/v2/downloads"
    Then the response status code should be 200