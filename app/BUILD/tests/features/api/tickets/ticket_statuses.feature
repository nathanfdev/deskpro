@new
Feature: /ticket_statuses endpoint

  Background:
    Given I'm authenticated as admin
    And only the following TicketStatus records exist:
      | #  | StatusType     | SysId        | Title    |
      | s1 | hidden         | spam         | Spam     |
      | s2 | hidden         | deleted      | Deleted  |
      | s3 | awaiting_agent | agent_1      | Agent 1  |
      | s4 | awaiting_user  |              | User 1   |
      | s5 | awaiting_user  |              | User 2   |
      | s6 | awaiting_agent |              | Agent 2   |

  Scenario: I retrieve a list of statuses
    When I send a GET request to "/api/v2/ticket_statuses"
    Then the JSON node "data" should have 6 elements
    And the JSON node "data[0].status_code" should be equal to "hidden.{s1}"
    And the JSON node "data[1].status_code" should be equal to "hidden.{s2}"
    And the JSON node "data[2].status_code" should be equal to "awaiting_agent.{s3}"
    And the JSON node "data[3].status_code" should be equal to "awaiting_user.{s4}"

  Scenario: I get a status
    When I send a GET request to "/api/v2/ticket_statuses/{s1}"
    Then the response status code should be 200
    And the JSON node "data.status_code" should be equal to "hidden.{s1}"

  Scenario: I get a status by status code
    When I send a GET request to "/api/v2/ticket_statuses/hidden.{s1}"
    Then the response status code should be 200
    And the JSON node "data.status_code" should be equal to "hidden.{s1}"

  Scenario: I create a status
    When I send a POST request to "/api/v2/ticket_statuses" with body:
    """
{
  "status_type": "awaiting_agent",
  "title": "Agent 2",
  "sys_id": "agent_2",
  "parent": ~s3~,
  "display_order": 2
}
    """
    Then the response status code should be 201
    And the JSON node "data.status_code" should exist
    And the JSON node "data.title" should be equal to "Agent 2"
    And the JSON node "data.sys_id" should be equal to "agent_2"
    And the JSON node "data.display_order" should be equal to 2

  Scenario: I should not be able to create hidden or archived sub status
    When I send a POST request to "/api/v2/ticket_statuses" with body:
    """
{
  "status_type": "hidden",
  "title": "Hidden",
  "sys_id": "hidden"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.status_type.errors[0].message" should exist

    When I send a POST request to "/api/v2/ticket_statuses" with body:
    """
{
  "status_type": "archived",
  "title": "Archived",
  "sys_id": "archived"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.status_type.errors[0].message" should exist

  Scenario: I should not be able to create status with duplicated sys_id
    When I send a POST request to "/api/v2/ticket_statuses" with body:
    """
{
  "status_type": "awaiting_agent",
  "title": "Agent 3",
  "sys_id": "agent_1"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "unique_entity"


  Scenario: I update a status
    When I send a PUT request to "/api/v2/ticket_statuses/{s3}" with body:
    """
{
  "status_type": "awaiting_user",
  "title": "Agent 2 1",
  "sys_id": "agent_2_1",
  "parent": ~s2~,
  "display_order": 3
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_statuses/{s3}"
    Then the response status code should be 200
    And the JSON node "data.status_code" should exist
    And the JSON node "data.status_type" should be equal to "awaiting_agent"
    And the JSON node "data.title" should be equal to "Agent 2 1"
    And the JSON node "data.sys_id" should be equal to "agent_2_1"
    And the JSON node "data.display_order" should be equal to 3

  Scenario: I should not be able to delete status with sys_id set
    When I send a DELETE request to "/api/v2/ticket_statuses/{s3}"
    Then the response status code should be 400

  Scenario: I should be able to delete status with empty sys_id
    When I send a DELETE request to "/api/v2/ticket_statuses/{s4}"
    Then the response status code should be 200

  Scenario: Delete status and set new status for tickets
    Given only the following Ticket records exist:
      | #       | Subject            | Status         | TicketStatus  |
      | ticket1 | First Demo Ticket  | awaiting_agent | {s6}          |

    When I send a DELETE request to "/api/v2/ticket_statuses/{s6}?set_to={s5:status_code}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "awaiting_user.{s5}"

    When I send a DELETE request to "/api/v2/ticket_statuses/{s5}?set_to={s4:status_code}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "awaiting_user.{s4}"

    When I send a DELETE request to "/api/v2/ticket_statuses/{s4}?set_to=awaiting_agent"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "awaiting_agent"
