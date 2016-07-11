@new
Feature: /feedback_comments/counts endpoint
  To retrieve count of feedback comments to validate
  As an API user
  I want an endpoint for feedback comments counts

  Background:
    Given I'm authenticated as admin
    And no "Feedback" records exist
    And only the following "FeedbackStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | fsc1 | active      | Collected | 0             |
      | fsc2 | active      | Accepted  | 0             |
    And only the following "FeedbackCategory" records exist:
      | #   | title      | slug       |
      | fc1 | Feature    | feature    |
      | fc2 | Suggestion | suggestion |

  Scenario: I GET feedback comments list awaiting review and side-loaded author info
    Given only the following "Feedback" records exist:
      | #  | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | f1 | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
    And only the following "FeedbackComment" records exist:
      | feedback | person  | content  | is_reviewed |
      | {f1}     | {admin} | comment1 | 0           |
      | {f1}     | {admin} | comment2 | 0           |
      | {f1}     | {admin} | comment3 | 0           |
      | {f1}     | {admin} | comment4 | 0           |
    When I send a GET request to "/api/v2/feedback_comments?include=person&awaiting_validation=1&order_by=date_created"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 4 elements
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 1 element

  Scenario: I GET count of feedback comment awaiting review
    Given only the following "Feedback" records exist:
      | #  | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | f1 | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
    And only the following "FeedbackComment" records exist:
      | feedback | person  | content  | is_reviewed |
      | {f1}     | {admin} | comment1 | 0           |
      | {f1}     | {admin} | comment2 | 0           |
      | {f1}     | {admin} | comment3 | 0           |
      | {f1}     | {admin} | comment4 | 0           |
    When I send a GET request to "/api/v2/feedback_comments/counts?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.nested" should have 0 elements

  Scenario: I GET count of feedback comment counter
    Given only the following "Feedback" records exist:
      | #  | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | f1 | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
      | f2 | {fsc2}          | {fc2}    | {admin} | 0           | feedback2 | Feedback2 | Feedback2 | active |
    And only the following "FeedbackComment" records exist:
      | feedback | person  | content  | is_reviewed |
      | {f1}     | {admin} | comment1 | 0           |
      | {f1}     | {admin} | comment2 | 0           |
      | {f2}     | {admin} | comment3 | 0           |
      | {f2}     | {admin} | comment4 | 0           |
    When I send a GET request to "/api/v2/feedback_comments/counts?group_by=feedback"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 4
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].title" should be equal to "Feedback1"

    And the JSON node "data.nested[1].count" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "Feedback2"

  Scenario: I GET count of feedback comment counter and filter them by feedback_id
    Given only the following "Feedback" records exist:
      | #  | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | f1 | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
      | f2 | {fsc2}          | {fc2}    | {admin} | 0           | feedback2 | Feedback2 | Feedback2 | active |
      | f3 | {fsc2}          | {fc2}    | {admin} | 0           | feedback3 | Feedback3 | Feedback3 | active |
    And only the following "FeedbackComment" records exist:
      | feedback | person  | content  | is_reviewed |
      | {f1}     | {admin} | comment1 | 0           |
      | {f1}     | {admin} | comment2 | 0           |
      | {f2}     | {admin} | comment3 | 0           |
    When I send a GET request to "/api/v2/feedback_comments/counts?group_by=feedback&feedback_ids[]={f1}&feedback_ids[]={f2}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].title" should be equal to "Feedback1"

    And the JSON node "data.nested[1].count" should be equal to 1
    And the JSON node "data.nested[1].title" should be equal to "Feedback2"

  Scenario: I DELETE feedback comment with id=1
    Given only the following "Feedback" records exist:
      | #  | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | f1 | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
    And only the following "FeedbackComment" records exist:
      | #        | feedback | person  | content  | is_reviewed |
      | comment1 | {f1}     | {admin} | comment1 | 0           |

    When I send a DELETE request to "/api/v2/feedback_comments/{comment1}"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I DELETE feedback comment with id=404404 (non-existent)
    When I send a DELETE request to "/api/v2/feedback_comments/404404"
    Then the response should be in JSON
    And the response status code should be 404
    And the JSON node "status" should be equal to 404
    And the JSON node "message" should be equal to "#404404 Not Found"
