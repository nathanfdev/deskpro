@feedback-nav
Feature: /feedback/filter endpoint
  To obtain values for different types of filter
  As a developer
  I want an endpoint for filter values

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET values for types of feedback (FeedbackCategory)
    When I send a GET request to "/api/v2/feedback/filter?name=type"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta.count" should be equal to 7
    And the JSON node "data[0].title" should be equal to "Gathering Feedback"
    And the JSON node "data[1].title" should be equal to "Planning"
    And the JSON node "data[2].title" should be equal to "Started"
    And the JSON node "data[3].title" should be equal to "Under Review"
    And the JSON node "data[4].title" should be equal to "Completed"
    And the JSON node "data[5].title" should be equal to "Duplicate"
    And the JSON node "data[6].title" should be equal to "Declined"

  Scenario: I GET values for categories of feedback (Feedback Custom Category)
    When I send a GET request to "/api/v2/feedback/filter?name=category"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta.count" should be equal to 6
    And the JSON node "data[0].title" should be equal to "Test feedback category 1"
    And the JSON node "data[1].title" should be equal to "Test feedback category 2"
    And the JSON node "data[2].title" should be equal to "Test feedback category 3"
    And the JSON node "data[3].title" should be equal to "Test feedback category 4"
    And the JSON node "data[4].title" should be equal to "Test feedback category 5"
    And the JSON node "data[5].title" should be equal to "Test feedback category 6"

