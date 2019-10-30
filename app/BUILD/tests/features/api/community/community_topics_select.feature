@new
Feature: /community_topics endpoint
  To obtain filtered list of community topics
  As an API user
  I want an endpoint for community topics select

  Background:
    Given I'm authenticated as "admin"
    And I set permission "community.use" = 1 for "registered" usergroup
    And no "CommunityTopic" records exist
    And only the following "CommunityTopicStatusCategory" records exist:
      | #     | status_type | title     | display_order |
      | ctsc1 | active      | Collected | 0             |
      | ctsc2 | active      | Accepted  | 0             |
      | ctsc3 | closed      | Declined  | 0             |
      | ctsc4 | closed      | Spam      | 0             |
    And only the following "CommunityForum" records exist:
      | #   | title    | slug     |
      | cc1 | Feature  | feature  |
      | cc2 | Question | question |
      | cc3 | Garbage  | garbage  |
    And only the following "CommunityTopic" records exist:
      | #   | status_category | forum   | person  | is_reviewed | slug   | title  | content | status | date_created | num_ratings | total_rating |
      | ct1 | {ctsc1}         | {cc1}   | {admin} | 0           | topic1 | Topic1 | Topic1  | active | 2015-01-01   | 0           | 0            |
      | ct2 | {ctsc2}         | {cc2}   | {admin} | 0           | topic2 | Topic2 | Topic2  | active | 2015-02-01   | 0           | 0            |
      | ct3 | {ctsc3}         | {cc3}   | {admin} | 0           | topic3 | Topic3 | Topic3  | closed | 2015-03-01   | 0           | 0            |
      | ct4 | {ctsc1}         | {cc3}   | {admin} | 0           | topic4 | Topic4 | Topic4  | active | 2015-04-01   | 0           | 0            |
      | ct5 | {ctsc2}         | {cc2}   | {admin} | 0           | topic5 | Topic5 | Topic5  | active | 2015-05-01   | 6           | 5            |
      | ct6 | {ctsc3}         | {cc1}   | {admin} | 0           | topic6 | Topic6 | Topic6  | closed | 2015-06-01   | 6           | 5            |

  Scenario: I GET list of community topics with hidden_status set to validating
    When I send a GET request to "/api/v2/community_topics?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6

  Scenario: I GET list of community topics with hidden_status set to validating and side-loaded author info
    When I send a GET request to "/api/v2/community_topics?include=person&awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 1 element
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6

  Scenario: I GET list of community topics with hidden_status set to validating and pagination set to 2 results per page
    When I send a GET request to "/api/v2/community_topics?awaiting_validation=1&count=2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6
    And the JSON node "meta.pagination.count" should be equal to 2
    And the JSON node "meta.pagination.per_page" should be equal to 2
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 3

  Scenario: I GET list of community topics with active status category
    When I send a GET request to "/api/v2/community_topics?status=active&status_category={ctsc1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 2

  Scenario: I GET list of active community topics
    When I send a GET request to "/api/v2/community_topics?status=active"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 4

  Scenario: I GET list of community topics from one forum
    When I send a GET request to "/api/v2/community_topics?forum=Garbage"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 2

  Scenario: I GET list of community topics tagged with one label
    Given only the following custom community fields exist:
      | #     | parent  | app_id | sys_name | js_class | has_form_template | has_display_template | title   | description | handler_class                                           | options | is_user_enabled | is_enabled | display_order | default_value | is_agent_field |
      | cdct1 |         |        | cat      |          | 0                 | 0                    | Forum   | Forum       | Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice |         | 1               | 1          | 0             |               | 1              |
      | cdct2 | {cdct1} |        |          |          | 0                 | 0                    | Windows |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdct3 | {cdct1} |        |          |          | 0                 | 0                    | Mac     |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdct4 | {cdct1} |        |          |          | 0                 | 0                    | Linux   |             |                                                         |         | 1               | 1          | 0             |               | 1              |
    And only the following "CustomDataCommunityTopic" records exist:
      | topic | field   | root_field | value |
      | {ct1} | {cdct2} | {cdct1}    | 0     |
      | {ct2} | {cdct2} | {cdct1}    | 0     |
      | {ct3} | {cdct3} | {cdct1}    | 0     |
      | {ct4} | {cdct4} | {cdct1}    | 0     |
    When I send a GET request to "/api/v2/community_topics?category=Windows"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.count" should be equal to 2

  Scenario: I GET community topics without any label
    Given only the following "LabelDef" records exist:
      | label_type | label | color | total |
      | community  | l1    | red   | 0     |
      | community  | l2    | blue  | 0     |
      | community  | l3    | white | 0     |
    And only the following "LabelCommunityTopic" records exist:
      | topic | label |
      | {ct1} | l1    |
      | {ct2} | l2    |
      | {ct3} | l3    |
    When I send a GET request to "/api/v2/community_topics?no_labels=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 3

  Scenario: I GET community topics with any label
    Given only the following "LabelDef" records exist:
      | label_type | label | color | total |
      | community  | l1    | red   | 0     |
      | community  | l2    | blue  | 0     |
      | community  | l3    | white | 0     |
    And only the following "LabelCommunityTopic" records exist:
      | topic | label |
      | {ct1} | l1    |
      | {ct2} | l2    |
      | {ct2} | l1    |
    When I send a GET request to "/api/v2/community_topics?label[]=l1&label[]=l2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].labels" should have 2 element
    And the JSON node "data[0].labels[0]" should be equal to "l1"
    And the JSON node "data[1].labels" should have 1 elements
    And the JSON node "data[1].labels[0]" should be equal to "l1"

  Scenario: I GET community topics with all labels
    Given only the following "LabelDef" records exist:
      | label_type | label | color | total |
      | community  | l1    | red   | 0     |
      | community  | l2    | blue  | 0     |
      | community  | l3    | white | 0     |
    And only the following "LabelCommunityTopic" records exist:
      | topic | label |
      | {ct1} | l1    |
      | {ct2} | l2    |
      | {ct2} | l1    |
    When I send a GET request to "/api/v2/community_topics?label[]=l1&label[]=l2&labels_mode=all"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element

    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "l1"
    And the JSON node "data[0].labels[1]" should be equal to "l2"

  Scenario: I GET community topics created after 2015-08-01
    When I send a GET request to "/api/v2/community_topics?created_from=2015-01-01"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6

  Scenario: I GET community topics created after 2015-02-01 but before 2015-05-01
    When I send a GET request to "/api/v2/community_topics?created_from=2015-02-01&created_to=2015-05-01"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 4

  Scenario: I check community topics comments count
    And only the following "CommunityTopicComment" records exist:
      | topic | person  | content   | is_reviewed |
      | {ct1} | {admin} | comment11 | 0           |
      | {ct1} | {admin} | comment12 | 0           |
      | {ct1} | {admin} | comment13 | 0           |
      | {ct2} | {admin} | comment21 | 0           |
      | {ct3} | {admin} | comment31 | 0           |
      | {ct4} | {admin} | comment41 | 0           |
      | {ct4} | {admin} | comment42 | 0           |
      | {ct4} | {admin} | comment43 | 0           |
    When I send a GET request to "/api/v2/community_topics?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data[0].id" should be equal to "{ct1}"
    And the JSON node "data[0].comments_count" should be equal to 3
    And the JSON node "data[1].id" should be equal to "{ct2}"
    And the JSON node "data[1].comments_count" should be equal to 1
    And the JSON node "data[2].id" should be equal to "{ct3}"
    And the JSON node "data[2].comments_count" should be equal to 1
    And the JSON node "data[3].id" should be equal to "{ct4}"
    And the JSON node "data[3].comments_count" should be equal to 3
    And the JSON node "data[4].id" should be equal to "{ct5}"
    And the JSON node "data[4].comments_count" should be equal to 0

  Scenario Outline: I order list
    When I send a GET request to "/api/v2/community_topics?order_by=<order_by>&order_dir=asc"
    Then the response status code should be 200
    And print last JSON response


    When I send a GET request to "/api/v2/community_topics?order_by=<order_by>&order_dir=desc"
    Then the response status code should be 200



    Examples:
      | order_by     | min1                       | min2                       | max1                       | max2                       |
      | forum        | "~cc1~"                    | "~cc1~"                    | "~cc3~"                    | "~cc3~"                    |
