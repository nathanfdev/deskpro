@new
Feature: /mass_actions/community_topic_comments endpoint
  To complete mass actions on community topics comments list
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"

  Scenario: I approve community topic comment
    Given no "CommunityTopic" records exist
    And only the following "CommunityTopicStatusCategory" records exist:
      | #     | status_type | title     | display_order |
      | ctsc1 | active      | Collected | 0             |
    And only the following "CommunityChannel" records exist:
      | #   | title   | slug    |
      | cc1 | Feature | feature |
    And only the following "CommunityTopic" records exist:
      | #     | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | topic | {ctsc1}         | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | #        | topic   | person  | content  | is_reviewed |
      | comment1 | {topic} | {admin} | comment1 | 0           |
      | comment2 | {topic} | {admin} | comment2 | 0           |
    When I send a POST request to "/api/v2/mass_actions/community_topic_comments" with body:
    """
{
  "ids": [~comment1~, ~comment2~],
  "params":{
     "set_of_actions": ["approve"]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/community_topic_comments"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0].status" should be equal to "visible"
    And the JSON node "data[1].status" should be equal to "visible"
