Feature: /task_links endpoint
  To CRUD DeskPRO task_links
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a linked article to a task
    When I send a POST request to "/api/v2/task_links/article" with body:
    """
{
  "task": 1,
  "article": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/task_links/1"
    And the JSON node "data" should exist
    And the JSON node "data.task" should be equal to 1

  Scenario: I try to link the same article to the same task
    When I send a POST request to "/api/v2/task_links/article" with body:
    """
{
  "task": 1,
  "article": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I GET a single member
    When I send a GET request to "/api/v2/task_links/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.task" should be equal to 1
    And the JSON node "data.article" should be equal to 1

  Scenario: I GET all linked articles
    When I send a GET request to "/api/v2/task_links?type=article"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].task" should be equal to 1
    And the JSON node "data[0].article" should be equal to 1

  Scenario: I GET all linked items for a particular task
    When I send a GET request to "/api/v2/tasks/1/linked_items/articles"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[0].article" should be equal to 1

  Scenario: I modify a linked item
    When I send a PUT request to "/api/v2/task_links/article/1" with body:
    """
{
  "article": 2
}
    """
    Then the response should be in JSON
    And the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/task_links/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.task" should be equal to "1"
    And the JSON node "data.article" should be equal to "2"

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/task_links/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/task_links/1"
    Then the response should be in JSON
    And the response status code should be 404

  Scenario: Successfully create a linked ticket to a task
    When I send a POST request to "/api/v2/task_links/ticket" with body:
    """
{
  "task": 1,
  "ticket": 2
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/task_links/3"
    And the JSON node "data" should exist
    And the JSON node "data.task" should be equal to 1
    And the JSON node "data.ticket" should be equal to 2

    Scenario: Successfully create a linked chat to a task
    When I send a POST request to "/api/v2/task_links/chat" with body:
    """
{
  "task": 1,
  "chat": 3
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/task_links/4"
    And the JSON node "data" should exist
    And the JSON node "data.task" should be equal to 1
    And the JSON node "data.chat" should be equal to 3
