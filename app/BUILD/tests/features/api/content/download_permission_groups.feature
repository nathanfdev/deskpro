@new
Feature: /downloads endpoint

  Scenario: I have no download permissions
    Given I'm authenticated as agent
    And there are no "Permission" records
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    When I send a GET request to "/api/v2/downloads"
    Then the response status code should be 403


  Scenario: I grant use download permission
    Given I'm authenticated as agent
    And there are no "Permission" records
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    And I set permission "downloads.use" = 1 for "registered" usergroup
    When I send a GET request to "/api/v2/downloads"
    Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given I'm authenticated as admin
    And there are no "Permission" records
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"
    When I send a GET request to "/api/v2/downloads"
    Then the response status code should be 200
