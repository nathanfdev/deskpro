@new
Feature: /community_topics/counts endpoint
  To obtain counters for different types of community topics
  As an API user
  I want an endpoint for community topics counts

  Background:
    Given I'm authenticated as "admin"
    And I set permission "community.use" = 1 for "registered" usergroup
    And no "CommunityTopic" records exist
    And only the following "CommunityTopicStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | csc1 | active      | Collected | 0             |
      | csc2 | active      | Accepted  | 0             |
      | csc3 | closed      | Declined  | 0             |
      | csc4 | closed      | Spam      | 0             |
    And only the following "CommunityChannel" records exist:
      | #   | title    | slug     |
      | cc1 | Feature  | feature  |
      | cc2 | Question | question |
      | cc3 | Garbage  | garbage  |

  Scenario: I GET count of community topics with hidden_status set to validating
    Given only the following "CommunityTopic" records exist:
      | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
      | {csc2}          | {cc2}   | {admin} | 0           | topic2 | Topic2 | Topic2  | active |
      | {csc3}          | {cc3}   | {admin} | 0           | topic3 | Topic3 | Topic3  | closed |
      | {csc1}          | {cc3}   | {admin} | 0           | topic4 | Topic4 | Topic4  | active |
      | {csc2}          | {cc2}   | {admin} | 0           | topic5 | Topic5 | Topic5  | active |
      | {csc3}          | {cc1}   | {admin} | 0           | topic6 | Topic6 | Topic6  | closed |
    When I send a GET request to "/api/v2/community_topics/counts?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 6

  Scenario: I GET count of community topics grouped by channel
    Given only the following "CommunityTopic" records exist:
      | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
      | {csc2}          | {cc2}   | {admin} | 0           | topic2 | Topic2 | Topic2  | active |
      | {csc3}          | {cc3}   | {admin} | 0           | topic3 | Topic3 | Topic3  | closed |
      | {csc1}          | {cc3}   | {admin} | 0           | topic4 | Topic4 | Topic4  | active |
      | {csc2}          | {cc2}   | {admin} | 0           | topic5 | Topic5 | Topic5  | active |
      | {csc3}          | {cc1}   | {admin} | 0           | topic6 | Topic6 | Topic6  | closed |
    When I send a GET request to "/api/v2/community_topics/counts?group_by=channel"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.nested" should exist
    And the JSON node "data.count" should be equal to 6

    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].title" should be equal to "Feature"

    And the JSON node "data.nested[1].count" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "Question"

    And the JSON node "data.nested[2].count" should be equal to 2
    And the JSON node "data.nested[2].title" should be equal to "Garbage"

  Scenario: I GET count of community topics with status active grouped by status_category
    Given only the following "CommunityTopic" records exist:
      | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
      | {csc2}          | {cc2}   | {admin} | 0           | topic2 | Topic2 | Topic2  | active |
      | {csc3}          | {cc3}   | {admin} | 0           | topic3 | Topic3 | Topic3  | closed |
      | {csc1}          | {cc3}   | {admin} | 0           | topic4 | Topic4 | Topic4  | active |
      | {csc2}          | {cc2}   | {admin} | 0           | topic5 | Topic5 | Topic5  | active |
      | {csc3}          | {cc1}   | {admin} | 0           | topic6 | Topic6 | Topic6  | closed |
    When I send a GET request to "/api/v2/community_topics/counts?status=active&group_by=status_category"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.grouped_by" should be equal to "status_category"

    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].title" should be equal to "Collected"

    And the JSON node "data.nested[1].count" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "Accepted"

  Scenario: I GET count of community topics grouped by custom_channel
    Given only the following custom community fields exist:
      | #     | parent  | app_id | sys_name | js_class | has_form_template | has_display_template | title    | description | handler_class                                           | options | is_user_enabled | is_enabled | display_order | default_value | is_agent_field |
      | cdct1 |         |        | cat      |          | 0                 | 0                    | Category | Category    | Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice |         | 1               | 1          | 0             |               | 1              |
      | cdct2 | {cdct1} |        |          |          | 0                 | 0                    | Windows  |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdct3 | {cdct1} |        |          |          | 0                 | 0                    | Mac      |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdct4 | {cdct1} |        |          |          | 0                 | 0                    | Linux    |             |                                                         |         | 1               | 1          | 0             |               | 1              |
    And only the following "CommunityTopic" records exist:
      | #   | status_category | channel | person  | is_reviewed | slug   | title  | content | status |
      | ct1 | {csc1}          | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active |
      | ct2 | {csc2}          | {cc2}   | {admin} | 0           | topic2 | Topic2 | Topic2  | active |
      | ct3 | {csc3}          | {cc3}   | {admin} | 0           | topic3 | Topic3 | Topic3  | closed |
      | ct4 | {csc1}          | {cc3}   | {admin} | 0           | topic4 | Topic4 | Topic4  | active |
      | ct5 | {csc2}          | {cc2}   | {admin} | 0           | topic5 | Topic5 | Topic5  | active |
      | ct6 | {csc3}          | {cc1}   | {admin} | 0           | topic6 | Topic6 | Topic6  | closed |
    And only the following "CustomDataCommunityTopic" records exist:
      | topic | field   | root_field | value |
      | {ct1} | {cdct2} | {cdct1}    | 0     |
      | {ct2} | {cdct2} | {cdct1}    | 0     |
      | {ct3} | {cdct3} | {cdct1}    | 0     |
      | {ct4} | {cdct4} | {cdct1}    | 0     |

    When I send a GET request to "/api/v2/community_topics/counts?group_by=custom_channel"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 6
    And the JSON node "data.grouped_by" should be equal to "custom_channel"

    And the JSON node "data.nested[0].title" should be equal to "Windows"
    And the JSON node "data.nested[0].count" should be equal to 2

    And the JSON node "data.nested[1].title" should be equal to "Mac"
    And the JSON node "data.nested[1].count" should be equal to 1

    And the JSON node "data.nested[2].title" should be equal to "Linux"
    And the JSON node "data.nested[2].count" should be equal to 1
