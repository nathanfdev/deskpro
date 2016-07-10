@new
Feature: /feedback endpoint
  I want to check permission groups

  Background:
    Given I'm authenticated as "agent"
    Given "agent@deskpro.dev" agent exists
    And "admin@deskpro.dev" admin exists
    And no "Feedback" records exist
    And only the following "FeedbackStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | fsc1 | active      | Collected | 0             |
    And only the following "FeedbackCategory" records exist:
      | #   | title    | slug     |
      | fc1 | Feature  | feature  |
    Given only the following "Feedback" records exist:
      | #  | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | f1 | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
    And only the following "FeedbackComment" records exist:
      | #       | feedback | person  | content  | is_reviewed |
      | comment | {f1}     | {admin} | comment1 | 0           |
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

    Scenario: I have no feedback permissions
      Given I'm authenticated as "agent"
      When I send a GET request to "/api/v2/feedback"
      Then the response status code should be 403

      When I send a GET request to "/api/v2/feedback/{f1}"
      Then the response status code should be 403

      When I send a GET request to "/api/v2/feedback_comments"
      Then the response status code should be 403

      When I send a GET request to "/api/v2/feedback_comments/{comment}"
      Then the response status code should be 403

    Scenario: I grant use feedback permission
      Given I set permission "feedback.use" = 1 for "registered" usergroup
      And my request is authenticated to "agent"

      When I send a GET request to "/api/v2/feedback"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback/{f1}"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback_comments"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback_comments/{comment}"
      Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given I'm authenticated as "admin"
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    When I send a GET request to "/api/v2/feedback"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/feedback/{f1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/feedback_comments"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/feedback_comments/{comment}"
    Then the response status code should be 200