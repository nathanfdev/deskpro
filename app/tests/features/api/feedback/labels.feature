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
    And the JSON node "data[0]" should be equal to "bar"
    And the JSON node "data[1]" should be equal to "barfoo"
    And the JSON node "data[2]" should be equal to "foo"
    And the JSON node "data[3]" should be equal to "foobar"

  Scenario: I GET feedback labels with suggest
    When I send a GET request to "/api/v2/feedback_labels?term=fo"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0]" should be equal to "foo"
    And the JSON node "data[1]" should be equal to "foobar"
