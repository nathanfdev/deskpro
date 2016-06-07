Feature: /feedback endpoint
  I want to check permission groups

  Background:
    Given I install the api data set

    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

    Scenario: I have no feedback permissions
      Given my request is authenticated to "agent"
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
      And my request is authenticated to "agent"

      When I send a GET request to "/api/v2/feedback"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback/1"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback_comments"
      Then the response status code should be 200

      When I send a GET request to "/api/v2/feedback_comments/1"
      Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given my request is authenticated to "admin"
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    When I send a GET request to "/api/v2/feedback"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/feedback/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/feedback_comments"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/feedback_comments/1"
    Then the response status code should be 200