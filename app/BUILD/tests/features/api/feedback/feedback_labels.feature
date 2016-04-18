@feedback
Feature: /feedback_labels endpoint
  I want to get all feedback labels

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET list of feedback labels
    When I send a GET request to "/api/v2/feedback_labels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0]" should be equal to "another"
    And the JSON node "data[1]" should be equal to "label1"
    And the JSON node "data[2]" should be equal to "label2"
