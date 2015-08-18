@counts @crm-nav
Feature: /people/counts endpoint
  To retrieve counts of DeskPRO people
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I count agents filtering out soft-deleted ones
    When I send a GET request to "/api/v2/people/counts?is_agent=1&is_deleted=0"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2

  Scenario: I count soft-deleted people
    When I send a GET request to "/api/v2/people/counts?is_deleted=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 1
