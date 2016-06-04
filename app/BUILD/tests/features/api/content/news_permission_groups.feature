Feature: /news endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  Scenario: I have no news permissions
    Given my request is authenticated to "agent"
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 403

  Scenario: I grant use news permission
    Given my request is authenticated to "agent"
    And I set permission "news.use" = 1 for "registered" usergroup
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 200

  Scenario: Admin is allmighty and them doesn't care about permission groups
    Given my request is authenticated to "admin"
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"
    When I send a GET request to "/api/v2/downloads"
    Then the response status code should be 200