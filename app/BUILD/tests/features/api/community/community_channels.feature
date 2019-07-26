@new
Feature: /community_channels endpoint
  To retrieve info about community channels
  As an API user
  I want an endpoint for community channels

  Background:
    Given I'm authenticated as "admin"

  Scenario: I GET all community channels
    Given no "CommunityTopic" records exist
    And only the following "CommunityChannel" records exist:
      | #   | title    | slug     |
      | cc1 | Feature  | feature  |
      | cc2 | Question | question |
      | cc3 | Garbage  | garbage  |
    When I send a GET request to "/api/v2/community_channels"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].title" should be equal to "Feature"
    And the JSON node "data[1].title" should be equal to "Garbage"
    And the JSON node "data[2].title" should be equal to "Question"
