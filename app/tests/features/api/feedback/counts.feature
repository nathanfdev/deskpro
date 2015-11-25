@counts @feedback-nav
Feature: /feedback/counts endpoint
  To obtain counters for different types of feedback
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
    And the JSON node "data.count" should be equal to 37

  Scenario: I GET count of feedback grouped by category
    When I send a GET request to "/api/v2/feedback/counts?group_by=category"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.nested" should exist
    And the JSON node "data.count" should be equal to 64
    And the JSON node "data.nested[0].group" should be equal to "Bug Report"
    And the JSON node "data.nested[0].count" should be equal to 9
    And the JSON node "data.nested[1].group" should be equal to "Feature Request"
    And the JSON node "data.nested[1].count" should be equal to 18
    And the JSON node "data.nested[2].group" should be equal to "Suggestion"
    And the JSON node "data.nested[2].count" should be equal to 37

  Scenario: I GET count of feedback with status active grouped by status_category
    When I send a GET request to "/api/v2/feedback/counts?status=active&group_by=status_category"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 18
    And the JSON node "data.grouped_by" should be equal to "status_category"
    And the JSON node "data.nested[0].count" should be equal to 9
    And the JSON node "data.nested[0].group" should be equal to "Gathering Feedback"

  Scenario: I GET count of feedback grouped by custom_category
    When I send a GET request to "/api/v2/feedback/counts?group_by=custom_category"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.grouped_by" should be equal to "custom_category"
    And the JSON node "data.nested[0].group" should be equal to "Linux"
    And the JSON node "data.nested[0].count" should be equal to 2

