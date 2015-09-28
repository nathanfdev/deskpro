@feedback-nav
Feature: /feedback_types endpoint
  To retrieve info about feedback types
  As a developer
  I want an endpoint for feedback types

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET all feedback types
    When I send a GET request to "/api/v2/feedback_types"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 6 elements
    And the JSON node "data[0].title" should be equal to "Test feedback category 1"
    And the JSON node "data[1].title" should be equal to "Test feedback category 2"
    And the JSON node "data[2].title" should be equal to "Test feedback category 3"
    And the JSON node "data[3].title" should be equal to "Test feedback category 4"
    And the JSON node "data[4].title" should be equal to "Test feedback category 5"
    And the JSON node "data[5].title" should be equal to "Test feedback category 6"
