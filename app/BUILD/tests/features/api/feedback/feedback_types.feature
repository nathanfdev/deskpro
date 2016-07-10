@new
Feature: /feedback_types endpoint
  To retrieve info about feedback types
  As an API user
  I want an endpoint for feedback types

  Background:
    Given I'm authenticated as "admin"

  Scenario: I GET all feedback types
    Given no "Feedback" records exist
    And only the following "FeedbackCategory" records exist:
      | #   | title    | slug     |
      | fc1 | Feature  | feature  |
      | fc2 | Question | question |
      | fc3 | Garbage  | garbage  |
    When I send a GET request to "/api/v2/feedback_types"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].title" should be equal to "Feature"
    And the JSON node "data[1].title" should be equal to "Garbage"
    And the JSON node "data[2].title" should be equal to "Question"
