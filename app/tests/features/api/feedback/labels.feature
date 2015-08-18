Feature: /feedback_labels endpoint
  To retrieve info on feedback labels
  As a developer
  I want an endpoint for feedback labels

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET all feedback labels
    When I send a GET request to "/api/v2/feedback_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data.bar" should be equal to "1"
    And the JSON node "data.barfoo" should be equal to "1"
    And the JSON node "data.foo" should be equal to "3"
    And the JSON node "data.foobar" should be equal to "1"

  Scenario: I GET feedback labels with suggest
    When I send a GET request to "/api/v2/feedback_labels?term=fo"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data.foo" should be equal to "3"
    And the JSON node "data.foobar" should be equal to "1"
