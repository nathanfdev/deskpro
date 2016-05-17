Feature: /user_groups endpoint
  To retrieve DeskPRO user groups
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get list of all user groups
    When I send a GET request to "/api/v2/user_groups"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].title" should be equal to "Everyone"

  Scenario: I get a single user group
    When I send a GET request to "/api/v2/user_groups/3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "3"
    And the JSON node "data.title" should be equal to "Group 1"

  Scenario: I try to get with wrong type
    When I send a GET request to "/api/v2/user_groups/7"
    And the response status code should be 404

  Scenario: I try to get disabled group
    When I send a GET request to "/api/v2/user_groups/4"
    And the response status code should be 404
