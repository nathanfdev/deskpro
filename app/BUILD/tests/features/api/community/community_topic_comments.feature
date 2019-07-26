@new
Feature: /community_topic_comments/counts endpoint
  To retrieve count of community topic comments to validate
  As an API user
  I want an endpoint for community topic comments counts

  Background:
    Given I'm authenticated as admin
    And no "CommunityTopic" records exist
    And only the following "CommunityTopicStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | csc1 | active      | Collected | 0             |
      | csc2 | active      | Accepted  | 0             |
    And only the following "CommunityChannel" records exist:
      | #   | title      | slug       |
      | cc1 | Feature    | feature    |
      | cc2 | Suggestion | suggestion |

  Scenario: I GET community topic comments list awaiting review and side-loaded author info
    Given only the following "CommunityTopic" records exist:
      | #   | status_category | category | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1}    | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | topic | person  | content  | is_reviewed |
      | {ct1} | {admin} | comment1 | 0           |
      | {ct1} | {admin} | comment2 | 0           |
      | {ct1} | {admin} | comment3 | 0           |
      | {ct1} | {admin} | comment4 | 0           |
    When I send a GET request to "/api/v2/community_topic_comments?include=person&awaiting_validation=1&order_by=date_created"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 4 elements
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 1 element

  Scenario: I GET count of community topic comment awaiting review
    Given only the following "CommunityTopic" records exist:
      | #   | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | topic | person  | content  | is_reviewed |
      | {ct1} | {admin} | comment1 | 0           |
      | {ct1} | {admin} | comment2 | 0           |
      | {ct1} | {admin} | comment3 | 0           |
      | {ct1} | {admin} | comment4 | 0           |
    When I send a GET request to "/api/v2/community_topic_comments/counts?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.nested" should have 0 elements

  Scenario: I GET count of community topic comment counter
    Given only the following "CommunityTopic" records exist:
      | #   | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
      | ct2 | {csc2}          | {cc2}   | {admin} | 0           | topic2 | Topic2 | Topic2  | active |
    And only the following "CommunityTopicComment" records exist:
      | topic | person  | content  | is_reviewed |
      | {ct1} | {admin} | comment1 | 0           |
      | {ct1} | {admin} | comment2 | 0           |
      | {ct2} | {admin} | comment3 | 0           |
      | {ct2} | {admin} | comment4 | 0           |
    When I send a GET request to "/api/v2/community_topic_comments/counts?group_by=topic"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].title" should be equal to "Topic1"

    And the JSON node "data.nested[1].count" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "Topic2"

  Scenario: I GET count of community topic comment counter and filter them by topic_id
    Given only the following "CommunityTopic" records exist:
      | #   | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
      | ct2 | {csc2}          | {cc2}   | {admin} | 0           | topic2 | Topic2 | Topic2  | active |
      | ct3 | {csc2}          | {cc2}   | {admin} | 0           | topic3 | Topic3 | Topic3  | active |
    And only the following "CommunityTopicComment" records exist:
      | topic | person  | content  | is_reviewed |
      | {ct1} | {admin} | comment1 | 0           |
      | {ct1} | {admin} | comment2 | 0           |
      | {ct2} | {admin} | comment3 | 0           |
    When I send a GET request to "/api/v2/community_topic_comments/counts?group_by=topic&topic_ids[]={ct1}&topic_ids[]={ct2}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].title" should be equal to "Topic1"

    And the JSON node "data.nested[1].count" should be equal to 1
    And the JSON node "data.nested[1].title" should be equal to "Topic2"

  Scenario: I DELETE community topic comment with id=1
    Given only the following "CommunityTopic" records exist:
      | #   | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | #        | topic | person  | content  | is_reviewed |
      | comment1 | {ct1} | {admin} | comment1 | 0           |

    When I send a DELETE request to "/api/v2/community_topic_comments/{comment1}"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I DELETE community topic comment with id=404404 (non-existent)
    When I send a DELETE request to "/api/v2/community_topic_comments/404404"
    Then the response should be in JSON
    And the response status code should be 404
    And the JSON node "status" should be equal to 404
    And the JSON node "message" should be equal to "#404404 Not Found"
