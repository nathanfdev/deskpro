@tasks
Feature: /tasks endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And my request is authenticated
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  @reinstall
  Scenario: I have no access to use tasks
    When I send a GET request to "/api/v2/tasks"
    Then the response status code should be 403

    When I send a GET request to "/api/v2/tasks/1"
    Then the response status code should be 403

    When I send a POST request to "/api/v2/tasks"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/tasks/1"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/tasks/1"
    Then the response status code should be 403

  Scenario: I grant permission to use tasks
    Given I set permission "agent_tasks.use" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/tasks"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tasks/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/tasks"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/tasks/1"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/tasks/1"
    Then the response status code should be 200
