@new
Feature: /community_forums endpoint
  To retrieve info about community forums
  As an API user
  I want an endpoint for community forums

  Background:
    Given I'm authenticated as "admin"

  Scenario: I GET all community forums
    Given no "CommunityTopic" records exist
    And only the following "CommunityForum" records exist:
      | #   | title    | slug     | noun     | plural    |
      | cc1 | Feature  | feature  | Feature  | Features  |
      | cc2 | Question | question | Question | Questions |
      | cc3 | Garbage  | garbage  | Garbage  | Garbage   |
    When I send a GET request to "/api/v2/community_forums"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].title" should be equal to "Feature"
    And the JSON node "data[1].title" should be equal to "Garbage"
    And the JSON node "data[2].title" should be equal to "Question"
