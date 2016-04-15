@downloads
Feature: /news endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And my request is authenticated
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  @reinstall
  Scenario: I have no news permissions
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 403

  Scenario: I grant use news permission
    Given I set permission "news.use" = 1 for "registered" usergroup
    When I send a GET request to "/api/v2/news"
    Then the response status code should be 200
