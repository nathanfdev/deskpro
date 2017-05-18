@new
Feature: /mass_actions/feedback_comments endpoint
  To complete mass actions on feedback list
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"

  Scenario: I approve feedback comment
    Given no "Feedback" records exist
    And only the following "FeedbackStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | fsc1 | active      | Collected | 0             |
    And only the following "FeedbackCategory" records exist:
      | #   | title   | slug    |
      | fc1 | Feature | feature |
    And only the following "Feedback" records exist:
      | #        | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | feedback | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
    And only the following "FeedbackComment" records exist:
      | #        | feedback   | person  | content  | is_reviewed |
      | comment1 | {feedback} | {admin} | comment1 | 0           |
      | comment2 | {feedback} | {admin} | comment2 | 0           |
    When I send a POST request to "/api/v2/mass_actions/feedback_comments" with body:
    """
{
  "ids": [~comment1~, ~comment2~],
  "params":{
     "set_of_actions": ["approve"]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/feedback_comments"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0].status" should be equal to "visible"
    And the JSON node "data[1].status" should be equal to "visible"
