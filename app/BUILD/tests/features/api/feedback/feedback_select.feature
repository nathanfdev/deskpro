Feature: /feedback endpoint
  To obtain filtered list of feedback
  As an API user
  I want an endpoint for feedback select

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
    And only the following "Feedback" records exist:
      | #  | status_category | category | person  | is_reviewed | slug      | title     | content   | status | date_created | num_ratings | total_rating |
      | f1 | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active | 2015-01-01   | 0           | 0            |
      | f2 | {fsc2}          | {fc2}    | {admin} | 0           | feedback2 | Feedback2 | Feedback2 | active | 2015-02-01   | 0           | 0            |
      | f3 | {fsc3}          | {fc3}    | {admin} | 0           | feedback3 | Feedback3 | Feedback3 | closed | 2015-03-01   | 0           | 0            |
      | f4 | {fsc1}          | {fc3}    | {admin} | 0           | feedback4 | Feedback4 | Feedback4 | active | 2015-04-01   | 0           | 0            |
      | f5 | {fsc2}          | {fc2}    | {admin} | 0           | feedback5 | Feedback5 | Feedback5 | active | 2015-05-01   | 6           | 5            |
      | f6 | {fsc3}          | {fc1}    | {admin} | 0           | feedback6 | Feedback6 | Feedback6 | closed | 2015-06-01   | 6           | 5            |

  Scenario: I GET list of feedback with hidden_status set to validating
    When I send a GET request to "/api/v2/feedback?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6

  Scenario: I GET list of feedback with hidden_status set to validating and side-loaded author info
    When I send a GET request to "/api/v2/feedback?include=person&awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 1 element
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6

  Scenario: I GET list of feedback with hidden_status set to validating and pagination set to 2 results per page
    When I send a GET request to "/api/v2/feedback?awaiting_validation=1&count=2"
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

  Scenario: I GET list of feedback with active status category
    When I send a GET request to "/api/v2/feedback?status=active&status_category={fsc1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 2

  Scenario: I GET list of active feedback
    When I send a GET request to "/api/v2/feedback?status=active"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 4

  Scenario: I GET list of feedback from one category
    When I send a GET request to "/api/v2/feedback?category=Garbage"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 2

  Scenario: I GET list of feedback tagged with one label
    Given only the following custom feedback fields exist:
      | #    | parent | app_id | sys_name | js_class | has_form_template | has_display_template | title    | description | handler_class                                           | options | is_user_enabled | is_enabled | display_order | default_value | is_agent_field |
      | cdf1 |        |        | cat      |          | 0                 | 0                    | Category | Category    | Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice |         | 1               | 1          | 0             |               | 1              |
      | cdf2 | {cdf1} |        |          |          | 0                 | 0                    | Windows  |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf3 | {cdf1} |        |          |          | 0                 | 0                    | Mac      |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf4 | {cdf1} |        |          |          | 0                 | 0                    | Linux    |             |                                                         |         | 1               | 1          | 0             |               | 1              |
    And only the following "CustomDataFeedback" records exist:
      | feedback | field  | root_field | value |
      | {f1}     | {cdf2} | {cdf1}     | 0    |
      | {f2}     | {cdf2} | {cdf1}     | 0    |
      | {f3}     | {cdf3} | {cdf1}     | 0    |
      | {f4}     | {cdf4} | {cdf1}     | 0    |
    When I send a GET request to "/api/v2/feedback?custom_category=Windows"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.count" should be equal to 2

  Scenario: I GET feedback without any label
    Given only the following "LabelDef" records exist:
      | label_type | label | color | total |
      | feedback   |  l1   | red   | 0     |
      | feedback   |  l2   | blue  | 0     |
      | feedback   |  l3   | white | 0     |
    And only the following "LabelFeedback" records exist:
      | feedback | label |
      | {f1}     | l1    |
      | {f2}     | l2    |
      | {f3}     | l3    |
    When I send a GET request to "/api/v2/feedback?no_labels=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 3

  Scenario: I GET feedback with any label
    Given only the following "LabelDef" records exist:
      | label_type | label | color | total |
      | feedback   | l1    | red   | 0     |
      | feedback   | l2    | blue  | 0     |
      | feedback   | l3    | white | 0     |
    And only the following "LabelFeedback" records exist:
      | feedback | label |
      | {f1}     | l1    |
      | {f2}     | l2    |
      | {f2}     | l1    |
    When I send a GET request to "/api/v2/feedback?label[]=l1&label[]=l2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].labels" should have 2 element
    And the JSON node "data[0].labels[0]" should be equal to "l1"
    And the JSON node "data[1].labels" should have 1 elements
    And the JSON node "data[1].labels[0]" should be equal to "l1"

  Scenario: I GET feedback with all labels
    Given only the following "LabelDef" records exist:
      | label_type | label | color | total |
      | feedback   | l1    | red   | 0     |
      | feedback   | l2    | blue  | 0     |
      | feedback   | l3    | white | 0     |
    And only the following "LabelFeedback" records exist:
      | feedback | label |
      | {f1}     | l1    |
      | {f2}     | l2    |
      | {f2}     | l1    |
    When I send a GET request to "/api/v2/feedback?label[]=l1&label[]=l2&labels_mode=all"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element

    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "l1"
    And the JSON node "data[0].labels[1]" should be equal to "l2"

  Scenario: I GET feedback created after 2015-08-01
    When I send a GET request to "/api/v2/feedback?created_from=2015-01-01"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6

  Scenario: I GET feedback created after 2015-02-01 but before 2015-05-01
    When I send a GET request to "/api/v2/feedback?created_from=2015-02-01&created_to=2015-05-01"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 4

  Scenario: I check feedback comments count
    And only the following "FeedbackComment" records exist:
      | feedback | person  | content   | is_reviewed |
      | {f1}     | {admin} | comment11 | 0           |
      | {f1}     | {admin} | comment12 | 0           |
      | {f1}     | {admin} | comment13 | 0           |
      | {f2}     | {admin} | comment21 | 0           |
      | {f3}     | {admin} | comment31 | 0           |
      | {f4}     | {admin} | comment41 | 0           |
      | {f4}     | {admin} | comment42 | 0           |
      | {f4}     | {admin} | comment43 | 0           |
    When I send a GET request to "/api/v2/feedback?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data[0].id" should be equal to "{f1}"
    And the JSON node "data[0].comments_count" should be equal to 3
    And the JSON node "data[1].id" should be equal to "{f2}"
    And the JSON node "data[1].comments_count" should be equal to 1
    And the JSON node "data[2].id" should be equal to "{f3}"
    And the JSON node "data[2].comments_count" should be equal to 1
    And the JSON node "data[3].id" should be equal to "{f4}"
    And the JSON node "data[3].comments_count" should be equal to 3
    And the JSON node "data[4].id" should be equal to "{f5}"
    And the JSON node "data[4].comments_count" should be equal to 0

  Scenario Outline: I order list
    When I send a GET request to "/api/v2/feedback?order_by=<order_by>&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data[0].<order_by>" should be equal to <min1>
    And the JSON node "data[1].<order_by>" should be equal to <min2>

    When I send a GET request to "/api/v2/feedback?order_by=<order_by>&order_dir=desc"
    Then the response status code should be 200
    And the JSON node "data[0].<order_by>" should be equal to <max1>
    And the JSON node "data[1].<order_by>" should be equal to <max2>

    Examples:
      | order_by     | min1                       | min2                       | max1                       | max2                       |
      | date_created | "2015-01-01T00:00:00+0000" | "2015-02-01T00:00:00+0000" | "2015-06-01T00:00:00+0000" | "2015-05-01T00:00:00+0000" |
      | id           | "~f1~"                     | "~f2~"                     | "~f6~"                     | "~f5~"                     |
      | total_rating | 0                          | 0                          | 5                          | 5                          |
      | num_ratings  | 0                          | 0                          | 6                          | 6                          |
      | title        | "Feedback1"                | "Feedback2"                | "Feedback6"                | "Feedback5"                |
      | status       | active                     | active                     | closed                     | closed                     |
      | category     | "~fc1~"                    | "~fc1~"                    | "~fc3~"                    | "~fc3~"                    |
      | person       | "~admin~"                  | "~admin~"                  | "~admin~"                  | "~admin~"                  |
