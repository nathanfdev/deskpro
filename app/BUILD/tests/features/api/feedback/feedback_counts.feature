Feature: /feedback/counts endpoint
  To obtain counters for different types of feedback
  As an API user
  I want an endpoint for feedback counts

  Background:
    Given I'm authenticated as "admin"
    And I set permission "feedback.use" = 1 for "registered" usergroup
    And no "Feedback" records exist
    And only the following "FeedbackStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | fsc1 | active      | Collected | 0             |
      | fsc2 | active      | Accepted  | 0             |
      | fsc3 | closed      | Declined  | 0             |
      | fsc4 | closed      | Spam      | 0             |
    And only the following "FeedbackCategory" records exist:
      | #   | title    | slug     |
      | fc1 | Feature  | feature  |
      | fc2 | Question | question |
      | fc3 | Garbage  | garbage  |

  Scenario: I GET count of feedback with hidden_status set to validating
    Given only the following "Feedback" records exist:
      | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
      | {fsc2}          | {fc2}    | {admin} | 0           | feedback2 | Feedback2 | Feedback2 | active |
      | {fsc3}          | {fc3}    | {admin} | 0           | feedback3 | Feedback3 | Feedback3 | closed |
      | {fsc1}          | {fc3}    | {admin} | 0           | feedback4 | Feedback4 | Feedback4 | active |
      | {fsc2}          | {fc2}    | {admin} | 0           | feedback5 | Feedback5 | Feedback5 | active |
      | {fsc3}          | {fc1}    | {admin} | 0           | feedback6 | Feedback6 | Feedback6 | closed |
    When I send a GET request to "/api/v2/feedback/counts?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 6

  Scenario: I GET count of feedback grouped by category
    Given only the following "Feedback" records exist:
      | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
      | {fsc2}          | {fc2}    | {admin} | 0           | feedback2 | Feedback2 | Feedback2 | active |
      | {fsc3}          | {fc3}    | {admin} | 0           | feedback3 | Feedback3 | Feedback3 | closed |
      | {fsc1}          | {fc3}    | {admin} | 0           | feedback4 | Feedback4 | Feedback4 | active |
      | {fsc2}          | {fc2}    | {admin} | 0           | feedback5 | Feedback5 | Feedback5 | active |
      | {fsc3}          | {fc1}    | {admin} | 0           | feedback6 | Feedback6 | Feedback6 | closed |
    When I send a GET request to "/api/v2/feedback/counts?group_by=category"
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

  Scenario: I GET count of feedback with status active grouped by status_category
    Given only the following "Feedback" records exist:
      | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
      | {fsc2}          | {fc2}    | {admin} | 0           | feedback2 | Feedback2 | Feedback2 | active |
      | {fsc3}          | {fc3}    | {admin} | 0           | feedback3 | Feedback3 | Feedback3 | closed |
      | {fsc1}          | {fc3}    | {admin} | 0           | feedback4 | Feedback4 | Feedback4 | active |
      | {fsc2}          | {fc2}    | {admin} | 0           | feedback5 | Feedback5 | Feedback5 | active |
      | {fsc3}          | {fc1}    | {admin} | 0           | feedback6 | Feedback6 | Feedback6 | closed |
    When I send a GET request to "/api/v2/feedback/counts?status=active&group_by=status_category"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.grouped_by" should be equal to "status_category"

    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].title" should be equal to "Collected"

    And the JSON node "data.nested[1].count" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "Accepted"

  Scenario: I GET count of feedback grouped by custom_category
    Given only the following custom feedback fields exist:
      | #    | parent | app_id | sys_name | js_class | has_form_template | has_display_template | title    | description | handler_class                                           | options | is_user_enabled | is_enabled | display_order | default_value | is_agent_field |
      | cdf1 |        |        | cat      |          | 0                 | 0                    | Category | Category    | Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice |         | 1               | 1          | 0             |               | 1              |
      | cdf2 | {cdf1} |        |          |          | 0                 | 0                    | Windows  |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf3 | {cdf1} |        |          |          | 0                 | 0                    | Mac      |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf4 | {cdf1} |        |          |          | 0                 | 0                    | Linux    |             |                                                         |         | 1               | 1          | 0             |               | 1              |
    And only the following "Feedback" records exist:
      | #  | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | f1 | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
      | f2 | {fsc2}          | {fc2}    | {admin} | 0           | feedback2 | Feedback2 | Feedback2 | active |
      | f3 | {fsc3}          | {fc3}    | {admin} | 0           | feedback3 | Feedback3 | Feedback3 | closed |
      | f4 | {fsc1}          | {fc3}    | {admin} | 0           | feedback4 | Feedback4 | Feedback4 | active |
      | f5 | {fsc2}          | {fc2}    | {admin} | 0           | feedback5 | Feedback5 | Feedback5 | active |
      | f6 | {fsc3}          | {fc1}    | {admin} | 0           | feedback6 | Feedback6 | Feedback6 | closed |
    And only the following "CustomDataFeedback" records exist:
      | feedback | field  | root_field | value |
      | {f1}     | {cdf2} | {cdf1}     | 0    |
      | {f2}     | {cdf2} | {cdf1}     | 0    |
      | {f3}     | {cdf3} | {cdf1}     | 0    |
      | {f4}     | {cdf4} | {cdf1}     | 0    |

    When I send a GET request to "/api/v2/feedback/counts?group_by=custom_category"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 6
    And the JSON node "data.grouped_by" should be equal to "custom_category"

    And the JSON node "data.nested[0].title" should be equal to "Windows"
    And the JSON node "data.nested[0].count" should be equal to 2

    And the JSON node "data.nested[1].title" should be equal to "Mac"
    And the JSON node "data.nested[1].count" should be equal to 1

    And the JSON node "data.nested[2].title" should be equal to "Linux"
    And the JSON node "data.nested[2].count" should be equal to 1
