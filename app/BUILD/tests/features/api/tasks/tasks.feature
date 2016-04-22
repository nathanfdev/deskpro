@tasks-nav @tasks @basic
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a task
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "My test task"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test task"

  Scenario: I try to POST a broken task with no title
    When I send a POST request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I GET a single task
    When I send a GET request to "/api/v2/tasks/3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test task"

  Scenario: I GET tasks
    When I send a GET request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "meta.pagination.count" should be equal to 3
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 3

    And the JSON node "data[0].id" should be equal to 3
    And the JSON node "data[0].title" should be equal to "My test task"

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "An unassigned task"

  Scenario: I modify a task
    When I send a PUT request to "/api/v2/tasks/3" with body:
    """
{
  "title": "New task title"
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/tasks/3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "New task title"

  Scenario: I add labels to a task
    When I send a PUT request to "/api/v2/tasks/3" with body:
    """
{
  "labels": ["test", "test", "labels"]
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.labels" should exist
    And the JSON node "data.labels[0]" should be equal to "test"

  Scenario: I modify linked items
    When I send a PUT request to "/api/v2/tasks/3" with body:
    """
{
  "linked_articles": [1, 2],
  "linked_chats": [2],
  "linked_tickets": [1, 3]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tasks/3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.linked_articles" should have 2 elements
    And the JSON node "data.linked_articles[0]" should be equal to 1
    And the JSON node "data.linked_articles[1]" should be equal to 2

    And the JSON node "data.linked_chats" should have 1 element
    And the JSON node "data.linked_chats[0]" should be equal to 2

    And the JSON node "data.linked_tickets" should have 2 elements
    And the JSON node "data.linked_tickets[0]" should be equal to 1
    And the JSON node "data.linked_tickets[1]" should be equal to 3

    When I send a PUT request to "/api/v2/tasks/3" with body:
    """
{
  "linked_articles": [2],
  "linked_chats": [1],
  "linked_tickets": [2, 4]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tasks/3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.linked_articles" should have 1 elements
    And the JSON node "data.linked_articles[0]" should be equal to 2

    And the JSON node "data.linked_chats" should have 1 element
    And the JSON node "data.linked_chats[0]" should be equal to 1

    And the JSON node "data.linked_tickets" should have 2 elements
    And the JSON node "data.linked_tickets[0]" should be equal to 2
    And the JSON node "data.linked_tickets[1]" should be equal to 4

  Scenario: I select unknown agent
    When I send a PUT request to "/api/v2/tasks/3" with body:
    """
{
  "agents": [1, 10000]
}
    """
    And the response status code should be 400
    And the JSON node "errors.fields.agents.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.agents.errors[0].message" should be equal to "This data type is not is data type that was expected."

  Scenario: I modify assigned items
    When I send a PUT request to "/api/v2/tasks/3" with body:
    """
{
  "agents": [1, 3],
  "departments": [2],
  "teams": [1, 2]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tasks/3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.agents" should have 2 elements
    And the JSON node "data.agents[0]" should be equal to 1
    And the JSON node "data.agents[1]" should be equal to 3

    And the JSON node "data.departments" should have 1 element
    And the JSON node "data.departments[0]" should be equal to 2

    And the JSON node "data.teams" should have 2 elements
    And the JSON node "data.teams[0]" should be equal to 1
    And the JSON node "data.teams[1]" should be equal to 2

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/tasks/3"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/tasks/3"
    Then the response should be in JSON
    And the response status code should be 404
