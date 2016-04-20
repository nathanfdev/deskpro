@tasks
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want to check assignment filters

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I prepare tasks
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task 1",
  "agents": [1, 2],
  "teams": [1, 2],
  "departments": [1]
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task 2",
  "agents": [4],
  "teams": [1],
  "departments": [1, 2]
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I filter with no assignments
    When I send a GET request to "/api/v2/tasks?no_assignments=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "An unassigned task"
    And the JSON node "data[0].agents" should have 0 elements
    And the JSON node "data[0].teams" should have 0 elements
    And the JSON node "data[0].departments" should have 0 elements

  Scenario: I filter by assigned agent
    When I send a GET request to "/api/v2/tasks?assigned_agent[0]=1&assigned_agent[1]=4"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].title" should be equal to "A demo task"
    And the JSON node "data[1].title" should be equal to "Task 1"
    And the JSON node "data[2].title" should be equal to "Task 2"

  Scenario: I filter by complex criteria
    When I send a GET request to "/api/v2/tasks?assigned_agent[0]=1&assigned_agent[1]=4&not_assigned_department=2&order_by=assignee"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to "A demo task"
    And the JSON node "data[1].title" should be equal to "Task 1"
