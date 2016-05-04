@feedback-categories @feedback
Feature: /feedback_categories endpoint
  To retrieve info about feedback categories (custom_category)
  As a developer
  I want an endpoint for feedback categories

  Background:
    Given I install the "api" data set
    And my request is authenticated

  Scenario: I GET all feedback categories
    When I send a GET request to "/api/v2/feedback_categories"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].title" should be equal to "Linux"
    And the JSON node "data[1].title" should be equal to "Mac"
    And the JSON node "data[2].title" should be equal to "Windows"
