@new
Feature: /community_topics endpoint
  I want to check permission groups

  Background:
    Given I'm authenticated as "agent"
    And "admin@deskpro.dev" admin exists
    And no "CommunityTopic" records exist
    And only the following "CommunityTopicStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | csc1 | active      | Collected | 0             |
    And only the following "CommunityForum" records exist:
      | #   | title   | slug    |
      | cc1 | Feature | feature |
    And only the following "CommunityTopic" records exist:
      | #   | status_category | forum | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
    And only the following "CommunityTopicComment" records exist:
      | #       | topic | person  | content  | is_reviewed |
      | comment | {ct1} | {admin} | comment1 | 0           |
    And there are no "Permission" records


  Scenario: I have no community permissions
    Given I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

    When I send a GET request to "/api/v2/community_topics"
    Then the response status code should be 403

    When I send a GET request to "/api/v2/community_topics/{ct1}"
    Then the response status code should be 403

    When I send a GET request to "/api/v2/community_topic_comments"
    Then the response status code should be 403

    When I send a GET request to "/api/v2/community_topic_comments/{comment}"
    Then the response status code should be 403

  Scenario: I grant use community permission
    Given I set permission "community.use" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/community_topics"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topics/{ct1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topic_comments"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topic_comments/{comment}"
    Then the response status code should be 200

  Scenario: Admin has all safe permissions
    Given I'm authenticated as "admin"
    And I set permission "community.use" = 0 for "registered" usergroup
    And I remove "admin" usergroup relation "agent_all_perms"
    And I add "admin" usergroup relation "agent_all_safe_perms"

    When I send a GET request to "/api/v2/community_topics"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topics/{ct1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topic_comments"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topic_comments/{comment}"
    Then the response status code should be 200

  Scenario: Admin has all permissions
    Given I'm authenticated as "admin"
    And I set permission "community.use" = 0 for "registered" usergroup
    And I add "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    When I send a GET request to "/api/v2/community_topics"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topics/{ct1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topic_comments"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/community_topic_comments/{comment}"
    Then the response status code should be 200
