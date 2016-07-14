@new
Feature: task counts endpoints
  I want to check task counts

  Background:
    Given I'm authenticated as "admin"

  Scenario: I get group counts
    Given "tu1@deskpro.dev" user exists
    Given "tu2@deskpro.dev" user exists
    And only the following "Task" records exist:
      | #  | title | visibility | creator           |
      | t1 | Task1 | 1          | {tu1@deskpro.dev} |
      | t2 | Task2 | 1          | {tu1@deskpro.dev} |
      | t3 | Task3 | 1          | {tu1@deskpro.dev} |
      | t4 | Task4 | 1          | {tu2@deskpro.dev} |
      | t5 | Task5 | 1          | {tu2@deskpro.dev} |
      | t6 | Task6 | 1          | {tu2@deskpro.dev} |
    And only the following "TaskAssignment" records exist:
      | task | person |
      | {t1} | {me}   |
      | {t2} | {me}   |
    When I send a GET request to "/api/v2/tasks/counts/groups"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.grouped_by" should be equal to "group"
    And the JSON node "data.count" should be equal to 6

    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].type" should be equal to "all"

    And the JSON node "data.nested[1].count" should be equal to 2
    And the JSON node "data.nested[1].type" should be equal to "my"

    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].type" should be equal to "team"

    And the JSON node "data.nested[3].count" should be equal to 0
    And the JSON node "data.nested[3].type" should be equal to "department"

    And the JSON node "data.nested[4].count" should be equal to 0
    And the JSON node "data.nested[4].type" should be equal to "delegated"

    And the JSON node "data.nested[5].count" should be equal to 4
    And the JSON node "data.nested[5].type" should be equal to "unassigned"

  Scenario: I get group counts
    Given only the following "TaskProject" records exist:
      | #  | title          |
      | tp1 | Test project 1 |
      | tp2 | Test project 2 |
    And "ta1@deskpro.dev" agent exists
    And "ta2@deskpro.dev" agent exists
    And "ta3@deskpro.dev" agent exists
    And only the following "Task" records exist:
      | #  | title | visibility | project |
      | t1 | Task1 | 1          | {tp1}   |
      | t2 | Task2 | 1          | {tp1}   |
      | t3 | Task3 | 1          | {tp1}   |
      | t4 | Task4 | 1          | {tp2}   |
      | t5 | Task5 | 1          | {tp2}   |
      | t6 | Task6 | 1          | {tp2}   |
    And only the following "TaskAssignment" records exist:
      | task  | person            |
      | {t1} | {ta1@deskpro.dev} |
      | {t2} | {ta1@deskpro.dev} |
      | {t3} | {ta2@deskpro.dev} |
      | {t4} | {ta2@deskpro.dev} |
      | {t5} | {ta3@deskpro.dev} |
      | {t6} | {ta3@deskpro.dev} |
    When I send a GET request to "/api/v2/tasks/counts/agents"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.grouped_by" should be equal to "agent"
    And the JSON node "data.count" should be equal to 6
    And print last JSON response

    And the JSON node "data.nested[1].id" should be equal to "{ta1@deskpro.dev}"
    And the JSON node "data.nested[1].count" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "Agent Agent"

    And the JSON node "data.nested[2].id" should be equal to "{ta2@deskpro.dev}"
    And the JSON node "data.nested[2].count" should be equal to 2
    And the JSON node "data.nested[2].title" should be equal to "Agent Agent"

    And the JSON node "data.nested[3].id" should be equal to "{ta3@deskpro.dev}"
    And the JSON node "data.nested[3].count" should be equal to 2
    And the JSON node "data.nested[3].title" should be equal to "Agent Agent"

  Scenario: I get group counts
    Given only the following "TaskProject" records exist:
      | #  | title          |
      | t1 | Test project 1 |
      | t2 | Test project 2 |
    And only the following "Task" records exist:
      | title | visibility | project |
      | Task1 | 1          | {t1}    |
      | Task2 | 1          | {t1}    |
      | Task3 | 1          | {t1}    |
      | Task4 | 1          | {t2}    |
      | Task5 | 1          | {t2}    |
      | Task6 | 1          | {t2}    |

    When I send a GET request to "/api/v2/tasks/counts/projects"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.grouped_by" should be equal to "project"
    And the JSON node "data.count" should be equal to 6

    And the JSON node "data.nested[0].id" should be equal to "{t1}"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].title" should be equal to "Test project 1"

    And the JSON node "data.nested[1].id" should be equal to "{t2}"
    And the JSON node "data.nested[1].count" should be equal to 3
    And the JSON node "data.nested[1].title" should be equal to "Test project 2"
