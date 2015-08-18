@counts
Feature: /user_group/counts endpoint
  To retrieve number of users in DeskPRO user groups
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get counts of users for all user groups
    When I send a GET request to "/api/v2/user_groups/counts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.nested.counts" should have 2 elements
