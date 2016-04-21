@counts @crm-nav @people
Feature: /people/counts endpoint
  To retrieve counts of DeskPRO people
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I count agents filtering out soft-deleted ones, grouped by teams
    When I send a GET request to "/api/v2/people/counts?is_agent=1&is_deleted=0&group_by=agent_team"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 2
    And the JSON node "data.nested" should exist
    And the JSON node "data.nested[0].title" should be equal to "test team"
    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].type" should be equal to "agent_team"

  Scenario: I count users filtering out soft-deleted ones, grouped by user groups
    When I send a GET request to "/api/v2/people/counts?is_agent=0&is_deleted=0&group_by=user_group"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 1
    And the JSON node "data.nested" should exist
    And the JSON node "data.nested[0].title" should be equal to "Everyone"
    And the JSON node "data.nested[0].count" should be equal to 1
    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].type" should be equal to "user_group"

  Scenario: I count soft-deleted people
    When I send a GET request to "/api/v2/people/counts?is_deleted=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 1
