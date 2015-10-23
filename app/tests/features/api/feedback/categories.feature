@feedback-categories
Feature: /feedback_categories endpoint
  To retrieve info about feedback categories (custom_category)
  As a developer
  I want an endpoint for feedback categories

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET all feedback categories
    When I send a GET request to "/api/v2/feedback_categories"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].input" should be equal to "Windows"
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].input" should be equal to "Linux"
    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].input" should be equal to "Linux"
    And the JSON node "data[3].id" should be equal to 4
    And the JSON node "data[3].input" should be equal to "Mac"
    And the JSON node "meta.count" should be equal to 4

  Scenario: I GET feedback categories for feedback with id in [1,4]
    When I send a GET request to "/api/v2/feedback_categories?ids=1,4"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].input" should be equal to "Windows"
    And the JSON node "data[1].id" should be equal to 4
    And the JSON node "data[1].input" should be equal to "Mac"
    And the JSON node "meta.count" should be equal to 2
