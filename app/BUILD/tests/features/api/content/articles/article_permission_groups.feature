@new
Feature: /articles endpoint
  I want to check permission groups

  Background:
    Given no Usergroup records exist
    And I set permission "articles.use" = 0 for "everyone" usergroup
    And I set permission "articles.use" = 0 for "registered" usergroup

  Scenario: I have no article permissions
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/articles"
    Then the response status code should be 403

  Scenario: I grant use articles permission
    Given I'm authenticated as "agent"
    And I set permission "articles.use" = 1 for "registered" usergroup
    When I send a GET request to "/api/v2/articles"
    Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/articles"
    Then the response status code should be 200
