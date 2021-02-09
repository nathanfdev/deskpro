@new
Feature: /community_topic_comments endpoint
  To rate community topic comments
  As an API user
  I want an endpoint to rate community topic comments

  Background:
    Given I'm authenticated as admin
    And no "CommunityTopic" records exist
    And only the following "CommunityTopicStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | csc1 | active      | Collected | 0             |
      | csc2 | active      | Accepted  | 0             |
    And only the following "CommunityForum" records exist:
      | #   | title      | slug       | noun       | plural      | verb_action    |
      | cc1 | Feature    | feature    | Feature    | Features    | New Feature    |
      | cc2 | Suggestion | suggestion | Suggestion | Suggestions | New Suggestion |

  Scenario: I GET rate count of community topic comments
    Given only the following "CommunityTopic" records exist:
      | #   | status_category | forum | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1} | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | #    | topic | person  | content  | is_reviewed |
      | com1 | {ct1} | {admin} | comment1 | 0           |
      | com2 | {ct1} | {admin} | comment2 | 0           |
      | com3 | {ct1} | {admin} | comment3 | 0           |
      | com4 | {ct1} | {admin} | comment4 | 0           |
    When I send a GET request to "/api/v2/community_topic_comments/{com1}/rate_count"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[1].total" should exist


  Scenario: I upvote a community topic comment
    Given only the following "CommunityTopic" records exist:
      | #   | status_category | forum | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1} | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | #    | topic | person  | content  | is_reviewed |
      | com1 | {ct1} | {admin} | comment1 | 0           |
    When I send a POST request to "/api/v2/community_topic_comments/{com1}/rate" with body:
    """
{
  "upvote": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.total_rating" should exist

    And the JSON node "data.total_rating" should be equal to "1"


  Scenario: I downvote a community topic comment
    Given only the following "CommunityTopic" records exist:
      | #   | status_category | forum | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1} | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | #    | topic | person  | content  | is_reviewed |
      | com1 | {ct1} | {admin} | comment1 | 0           | 
    When I send a POST request to "/api/v2/community_topic_comments/{com1}/rate" with body:
    """
{
  "upvote": 0
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.total_rating" should exist

    And the JSON node "data.total_rating" should be equal to "-1"
