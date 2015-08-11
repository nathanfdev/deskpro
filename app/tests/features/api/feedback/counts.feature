Feature: /feedback/counts endpoint
  To retrieve count of feedback to validate
  As a developer
  I want an endpoint for feedback counts

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET count of feedback with hidden_status set to validating
    When I send a GET request to "/api/v2/feedback/counts?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[0]" should be equal to 1