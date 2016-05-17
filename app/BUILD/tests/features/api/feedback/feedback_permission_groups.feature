Feature: /feedback endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And my request is authenticated
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    Scenario: I have no feedback permissions
      When I send a GET request to "/api/v2/feedback"
      Then the response status code should be 403

      When I send a GET request to "/api/v2/feedback/1"
      Then the response status code should be 403

      When I send a GET request to "/api/v2/feedback_comments"
      Then the response status code should be 403

      When I send a GET request to "/api/v2/feedback_comments/1"
      Then the response status code should be 403

    Scenario: I grant use feedback permission
      Given I set permission "feedback.use" = 1 for "registered" usergroup

      When I send a GET request to "/api/v2/feedback"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback/1"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback_comments"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback_comments/1"
      Then the response status code should be 200
