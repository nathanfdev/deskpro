@new
Feature: /organizations/counts endpoint
  To retrieve number of DeskPRO organizations
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"

  Scenario: I get number of organizations
    Given only the following "Organization" records exist:
      | #             | name          |
      | organization  | Organization1 |
      | organization2 | Organization2 |
    When I send a GET request to "/api/v2/organizations/counts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2

  Scenario: I count organizations grouped by user groups
    Given only the following "Organization" records exist:
      | #             | name          |
      | organization  | Organization1 |
      | organization2 | Organization2 |
      | organization3 | Organization3 |
    When I send a GET request to "/api/v2/organizations/counts?group_by=user_group"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.nested" should exist
    And the JSON node "data.nested[0].title" should be equal to 0
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].type" should be equal to "user_group"
