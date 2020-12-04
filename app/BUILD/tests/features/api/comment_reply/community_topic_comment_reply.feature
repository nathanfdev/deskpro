@new
Feature: /community_topic_comments endpoint
  To reply community topic comment
  As an API user
  I want an endpoint to reply an community topic comment

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

  Scenario: I reply an community topic comment
    Given only the following "CommunityTopic" records exist:
      | #    | status_category | forum | person  | is_reviewed | slug   | title  | content | status |
      | art1 | {csc1}          | {cc1} | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | #    | topic        | person  | content            | is_reviewed  |
      | com1 | {art1}       | {admin} | comment1           | 1            |
    When I send a POST request to "/api/v2/community_topic_comments" with parameters:
      | key             | value                         |
      | content        | reply to community topic comment     |
      | status         | visible                      |
      | topic         | ~art1~                       |
      | parent_id    | ~com1~                       |
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.parent" should exist
    And the JSON node "data.parent" should be equal to "{com1}"


  Scenario: I reply an community topic comment with status "deleted"
    Given only the following "CommunityTopic" records exist:
      | #    | status_category | forum | person  | is_reviewed | slug   | title  | content | status |
      | art1 | {csc1}          | {cc1} | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | #    | topic  | person  | content          | is_reviewed     | status   |
      | com1 | {art1}    | {admin} | comment1      | 1              |  deleted |
    When I send a POST request to "/api/v2/community_topic_comments" with parameters:
      | key             | value                      |
      | content        | reply to topic comment      |
      | status         | visible                    |
      | topic         | ~art1~                      |
      | parent_id    | ~com1~                      |
    Then the response should be in JSON
    And the response status code should be 400



  Scenario: I reply an community topic comment with wrong parent_id and community topic combination
    Given only the following "CommunityTopic" records exist:
      | #    | status_category | forum | person  | is_reviewed | slug   | title  | content | status |
      | art1 | {csc1}          | {cc1} | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
      | art2 | {csc1}          | {cc1} | {admin} | 0           | topic2 | Topic2 | Topic2  | active |
    And only the following "CommunityTopicComment" records exist:
      | #    | topic       | person        | content      | is_reviewed | status    |
      | com1 | {art1}       | {admin}      | comment1      | 1           |  visible  |
      | com2 | {art2}        | {admin}      | comment2     | 1           |  visible  |
    When I send a POST request to "/api/v2/community_topic_comments" with parameters:
      | key             | value                     |
      | content         | reply to topic comment    |
      | status          | visible                    |
      | community topic       | ~art1~                     |
      | parent_id     | ~com2~                      |
    Then the response should be in JSON
    And the response status code should be 400
