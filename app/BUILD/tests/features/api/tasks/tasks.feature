@new
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And only the following Task records exist:
      | #    | creator | title     |
      | task | {me}    | Test Task |

  Scenario: I try to POST a broken task with no title
    When I send a POST request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

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

  Scenario: I GET a single task
    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "Test Task"

  Scenario: I GET tasks
    When I send a GET request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I modify a task
    When I send a PUT request to "/api/v2/tasks/{task}" with body:
    """
{
  "title": "New task title"
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "New task title"

  Scenario: I add labels to a task
    When I send a PUT request to "/api/v2/tasks/{task}" with body:
    """
{
  "labels": ["test", "labels"]
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.labels" should exist
    And the JSON node "data.labels[0]" should be equal to "test"

  Scenario: I modify linked items
    Given only the following "Article" records exist:
      | #        | slug     | title    | content  | status  |
      | article1 | article1 | Article1 | Article1 | visible |
      | article2 | article2 | Article2 | Article2 | visible |
    And only the following "Ticket" records exist:
      | #       | status        | ref  | subject |
      | ticket1 | awaiting_user | AAAA | Ticket1 |
      | ticket2 | awaiting_user | BBBB | Ticket2 |
    And only the following "Chat" records exist:
      | #     | subject |
      | chat1 | Chat1   |
      | chat2 | Chat2   |
    When I send a PUT request to "/api/v2/tasks/{task}" with body:
    """
{
  "linked_articles": [~article1~],
  "linked_chats": [~chat1~],
  "linked_tickets": [~ticket1~]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.linked_articles" should have 1 element
    And the JSON node "data.linked_articles[0]" should be equal to "{article1}"

    And the JSON node "data.linked_chats" should have 1 element
    And the JSON node "data.linked_chats[0]" should be equal to "{chat1}"

    And the JSON node "data.linked_tickets" should have 1 element
    And the JSON node "data.linked_tickets[0]" should be equal to "{ticket1}"

    When I send a PUT request to "/api/v2/tasks/{task}" with body:
    """
{
  "linked_articles": [~article2~],
  "linked_chats": [~chat2~],
  "linked_tickets": [~ticket2~]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.linked_articles" should have 1 element
    And the JSON node "data.linked_articles[0]" should be equal to "{article2}"

    And the JSON node "data.linked_chats" should have 1 element
    And the JSON node "data.linked_chats[0]" should be equal to "{chat2}"

    And the JSON node "data.linked_tickets" should have 1 element
    And the JSON node "data.linked_tickets[0]" should be equal to "{ticket2}"

  Scenario: I select unknown agent
    When I send a PUT request to "/api/v2/tasks/{task}" with body:
    """
{
  "agents": [1, 10000]
}
    """
    And the response status code should be 400
    And the JSON node "errors.fields.agents.errors[0].code" should be equal to "bad_choice"

  Scenario: I modify assigned items
    Given "ta1@deskpro.dev" agent exists
    And "ta2@deskpro.dev" agent exists
    And the following "Department" records exist:
      | #           | title       | is_tickets_enabled | is_chat_enabled |
      | department1 | Department1 | 1                  | 1               |
      | department2 | Department2 | 1                  | 1               |
    And the following "AgentTeam" records exist:
      | #          | name        |
      | agentTeam1 | Agent Team1 |
      | agentTeam2 | Agent Team2 |
    When I send a PUT request to "/api/v2/tasks/{task}" with body:
    """
{
  "agents": [~ta1@deskpro.dev~, ~ta2@deskpro.dev~],
  "departments": [~department1~, ~department2~],
  "teams": [~agentTeam1~, ~agentTeam2~]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.agents" should have 2 elements
    And the JSON node "data.agents[0]" should be equal to "{ta1@deskpro.dev}"
    And the JSON node "data.agents[1]" should be equal to "{ta2@deskpro.dev}"

    And the JSON node "data.departments" should have 2 element
    And the JSON node "data.departments[0]" should be equal to "{department1}"
    And the JSON node "data.departments[1]" should be equal to "{department2}"

    And the JSON node "data.teams" should have 2 elements
    And the JSON node "data.teams[0]" should be equal to "{agentTeam1}"
    And the JSON node "data.teams[1]" should be equal to "{agentTeam2}"

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
