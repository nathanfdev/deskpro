@feedback
Feature: /feedback_labels endpoint
  I want to get all feedback labels

  Background:
    Given I install the "api" data set
    And my request is authenticated

  Scenario: I GET list of feedback labels
    When I send a GET request to "/api/v2/feedback_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].label_type" should be equal to "feedback"
    And the JSON node "data[0].label" should be equal to "AAA-feedback"
    And the JSON node "data[1].label" should be equal to "BBB-feedback"
    And the JSON node "data[2].label" should be equal to "CCC-feedback"
