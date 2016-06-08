@new
Feature: /tasks endpoint
  I want to check permission groups

  Background:
    Given I'm authenticated as agent
    And I create a Task and reference it as task
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

  Scenario: I have no access to use tasks
    When I send a GET request to "/api/v2/tasks"
    Then the response status code should be 403

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tasks"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/tasks/{task}"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/tasks/{task}"
    Then the response status code should be 403

  Scenario: I grant permission to use tasks
    Given I set permission "agent_tasks.use" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/tasks"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tasks"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/tasks/{task}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/tasks/{task}"
    Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given I'm authenticated as admin
    And I create a Task and reference it as task
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    When I send a GET request to "/api/v2/tasks"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tasks"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/tasks/{task}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/tasks/{task}"
    Then the response status code should be 200