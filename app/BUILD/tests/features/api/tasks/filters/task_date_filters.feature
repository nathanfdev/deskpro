@tasks
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want to check date filters

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I prepare tasks
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task 1",
  "date_due": "2016-04-21",
  "date_done": "2016-04-19"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task 2",
  "date_due": "2016-04-24",
  "date_done": "2016-04-18"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I sort tasks by date due
    When I send a GET request to "/api/v2/tasks?order_by=date_due&order_dir=desc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].title" should be equal to "Task 2"
    And the JSON node "data[0].date_due" should be equal to "2016-04-24T00:00:00+0000"
    And the JSON node "data[1].title" should be equal to "Task 1"
    And the JSON node "data[1].date_due" should be equal to "2016-04-21T00:00:00+0000"
    And the JSON node "data[2].title" should be equal to "A demo task"
    And the JSON node "data[2].date_due" should be equal to 0
    And the JSON node "data[3].title" should be equal to "An unassigned task"
    And the JSON node "data[3].date_due" should be equal to 0

    When I send a GET request to "/api/v2/tasks?order_by=date_due&order_dir=asc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].title" should be equal to "A demo task"
    And the JSON node "data[0].date_due" should be equal to 0
    And the JSON node "data[1].title" should be equal to "An unassigned task"
    And the JSON node "data[1].date_due" should be equal to 0
    And the JSON node "data[2].title" should be equal to "Task 1"
    And the JSON node "data[2].date_due" should be equal to "2016-04-21T00:00:00+0000"
    And the JSON node "data[3].title" should be equal to "Task 2"
    And the JSON node "data[3].date_due" should be equal to "2016-04-24T00:00:00+0000"

  Scenario: I sort tasks by date done
    When I send a GET request to "/api/v2/tasks?order_by=date_done&order_dir=desc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].title" should be equal to "Task 1"
    And the JSON node "data[0].date_done" should be equal to "2016-04-19T00:00:00+0000"
    And the JSON node "data[1].title" should be equal to "Task 2"
    And the JSON node "data[1].date_done" should be equal to "2016-04-18T00:00:00+0000"
    And the JSON node "data[2].title" should be equal to "A demo task"
    And the JSON node "data[2].date_done" should be equal to 0
    And the JSON node "data[3].title" should be equal to "An unassigned task"
    And the JSON node "data[3].date_done" should be equal to 0

    When I send a GET request to "/api/v2/tasks?order_by=date_done&order_dir=asc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].title" should be equal to "A demo task"
    And the JSON node "data[0].date_done" should be equal to 0
    And the JSON node "data[1].title" should be equal to "An unassigned task"
    And the JSON node "data[1].date_done" should be equal to 0
    And the JSON node "data[2].title" should be equal to "Task 2"
    And the JSON node "data[2].date_done" should be equal to "2016-04-18T00:00:00+0000"
    And the JSON node "data[3].title" should be equal to "Task 1"
    And the JSON node "data[3].date_done" should be equal to "2016-04-19T00:00:00+0000"

  Scenario: I filter tasks by date due
    When I send a GET request to "/api/v2/tasks?due_from=2016-04-01&due_to=2016-04-30"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to "Task 1"
    And the JSON node "data[0].date_due" should be equal to "2016-04-21T00:00:00+0000"
    And the JSON node "data[1].title" should be equal to "Task 2"
    And the JSON node "data[1].date_due" should be equal to "2016-04-24T00:00:00+0000"

    When I send a GET request to "/api/v2/tasks?due_from=2016-04-22"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "Task 2"
    And the JSON node "data[0].date_due" should be equal to "2016-04-24T00:00:00+0000"

    When I send a GET request to "/api/v2/tasks?due_to=2016-04-21"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "Task 1"
    And the JSON node "data[0].date_due" should be equal to "2016-04-21T00:00:00+0000"

  Scenario: I filter tasks by date done
    When I send a GET request to "/api/v2/tasks?done_from=2016-04-01&done_to=2016-04-30"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to "Task 1"
    And the JSON node "data[0].date_done" should be equal to "2016-04-19T00:00:00+0000"
    And the JSON node "data[1].title" should be equal to "Task 2"
    And the JSON node "data[1].date_done" should be equal to "2016-04-18T00:00:00+0000"

    When I send a GET request to "/api/v2/tasks?done_from=2016-04-19"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "Task 1"
    And the JSON node "data[0].date_done" should be equal to "2016-04-19T00:00:00+0000"

    When I send a GET request to "/api/v2/tasks?done_to=2016-04-18"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "Task 2"
    And the JSON node "data[0].date_done" should be equal to "2016-04-18T00:00:00+0000"
