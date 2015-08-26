Feature: /feedback/ endpoint
  To obtain filtered list of feedback
  As a developer
  I want an endpoint for feedback select

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET list of feedback with hidden_status set to validating
    When I send a GET request to "/api/v2/feedback/?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 4 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 4

  Scenario: I GET list of feedback with active status category
    When I send a GET request to "/api/v2/feedback/?status=active&status_category=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 2

  Scenario: I GET list of active feedback
    When I send a GET request to "/api/v2/feedback/?status=active"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 2

  Scenario: I GET list of feedback from one category
    When I send a GET request to "/api/v2/feedback/?category=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 3

  Scenario: I GET list of feedback tagged with one label
    When I send a GET request to "/api/v2/feedback/?category=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 3
