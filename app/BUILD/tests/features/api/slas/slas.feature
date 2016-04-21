@basic
Feature: /slas endpoint
  To retrieve DeskPRO SLAs
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I get paginated list of SLAs
    When I send a GET request to "/api/v2/slas"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].apply_terms" should not exist
    And the JSON node "data[0].warn_actions" should not exist
    And the JSON node "data[0].fail_actions" should not exist

  Scenario: I get a single person
    When I send a GET request to "/api/v2/slas/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "First"
    And the JSON node "data.apply_terms" should exist
    And the JSON node "data.warn_actions" should exist
    And the JSON node "data.fail_actions" should exist
