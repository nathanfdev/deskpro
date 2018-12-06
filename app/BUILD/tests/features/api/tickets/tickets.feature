@new
Feature: /tickets endpoint
  To CRUD DeskPRO tickets
  As an API user
  I want an API endpoint

  Background:
    Given no Person records exist
    And no EmailAccount records exist
    And I'm authenticated as admin
    And I have only default brand
    And agent and user exist
    And only the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
      | b2 | Brand 2 |
      | b3 | Brand 3 |
    And only the following Department records exist:
      | #  | Title        | Brands      | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Department 1 | [{b1},{b2}] | 1                  | 1               |
      | d2 | Department 2 | [{b2}]      | 1                  | 1               |
      | d3 | Department 2 | [{b3}]      | 1                  | 1               |
    And only the following Organization records exist:
      | #         | Name                  |
      | microsoft | Microsoft Corporation |
    And only the following TicketStatus records exist:
      | #   | StatusType     | SysId        | Title    |
      | ts1 | hidden         | spam         | Spam     |
      | ts2 | hidden         | deleted      | Deleted  |
    And only the following Ticket records exist:
      | #       | Subject            | Agent   | Organization | Status         | TicketSTatus  |
      | ticket1 | First Demo Ticket  | {admin} | {microsoft}  | awaiting_user  |               |
      | ticket2 | Second Demo Ticket | {agent} |              | awaiting_agent |               |
      | ticket3 | Third Demo Ticket  | {agent} |              | resolved       |               |
      | ticket4 | Fourth Demo Ticket | {agent} |              | archived       |               |
      | ticket5 | Fifth Demo Ticket  | {agent} |              | hidden         | {ts2}         |
      | ticket6 | Six Demo Ticket    | {agent} |              | hidden         | {ts1}         |
    And there are no custom ticket fields defined

  Scenario: I create a ticket
    Given only the following Product records exist:
      | #  | Title     |
      | p1 | Product 1 |
      | p2 | Product 2 |
    When I send a POST request to "/api/v2/tickets?XDEBUG_SESSION_START=netbeans-xdebug" with body:
    """
{
  "subject": "Test Ticket",
  "parent": ~ticket1~,
  "department": ~d1~,
  "is_hold": true,
  "person":  ~user~,
  "agent": ~agent~,
  "product": ~p2~,
  "star": "green",
  "cc": ["user@deskpro.dev", "agent@deskpro.dev", ~admin~]
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/tickets/{lastCreatedId}"
    And the JSON node "data.subject" should be equal to "Test Ticket"
    And the JSON node "data.is_hold" should be equal to 1
    And the JSON node "data.parent" should be equal to "{ticket1}"
    And the JSON node "data.department" should be equal to "{d1}"
    And the JSON node "data.product" should be equal to "{p2}"
    And the JSON node "data.person" should be equal to "{user}"
    And the JSON node "data.agent" should be equal to "{agent}"
    And the JSON node "data.cc" should have 3 elements
    And the JSON node "data.cc[0]" should be equal to "{user}"
    And the JSON node "data.cc[1]" should be equal to "{agent}"
    And the JSON node "data.cc[2]" should be equal to "{admin}"
    And the JSON node "data.star" should be equal to the string "green"

  Scenario: I create a ticket with substatus
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket",
  "status": "hidden.~ts2~"
}
    """
    Then the response status code should be 201
    And the JSON node "data.subject" should be equal to "Test Ticket"
    And the JSON node "data.status" should be equal to "hidden.{ts2}"

  Scenario: I modify a ticket
    When I send a PUT request to "/api/v2/tickets/{ticket1}" with body:
    """
{
  "subject": "Modified 2"
}
    """
    Then the response status code should be 204

  Scenario: I modify a ticket`s dates
    Given "admin_for_tickets@deskpro.dev" admin exists
    And I'm authenticated as person with email "admin_for_tickets@deskpro.dev" with super key
    When I send a PUT request to "/api/v2/tickets/{ticket1}" with body:
    """
{
  "date_resolved": "2018-01-01 23:59:59",
  "date_archived": "2018-01-01 23:59:59",
  "date_feedback_rating": "2018-01-01 23:59:59",
  "date_first_agent_assign": "2018-01-01 23:59:59",
  "date_first_agent_reply": "2018-01-01 23:59:59",
  "date_last_agent_reply": "2018-01-01 23:59:59",
  "date_last_user_reply": "2018-01-01 23:59:59",
  "date_agent_waiting": "2018-01-01 23:59:59",
  "date_user_waiting": "2018-01-01 23:59:59",
  "date_status": "2018-01-01 23:59:59",
  "date_on_hold": "2018-01-01 23:59:59",
  "date_locked": "2018-01-01 23:59:59",
  "total_user_waiting": 100,
  "total_to_first_reply": 200
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{ticket1}"
    Then the response status code should be 200
    And print last JSON response
    And the JSON node "data.date_resolved" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_archived" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_feedback_rating" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_first_agent_assign" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_first_agent_reply" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_last_agent_reply" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_last_user_reply" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_agent_waiting" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_status" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_on_hold" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_locked" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.total_user_waiting" should be equal to 100
    And the JSON node "data.total_to_first_reply" should be equal to 200

  Scenario: I modify and retrieve a ticket
    Given I send a PUT request to "/api/v2/tickets/{ticket1}" with body:
    """
{
  "subject": "Modified 4",
  "cc": [~agent~, ~admin~]
}
    """
    And the response status code should be 204
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    Then the response status code should be 200
    And the JSON node "data.subject" should be equal to "Modified 4"
    And the JSON node "data.cc" should have 2 elements

  Scenario: I modify and retrieve a ticket by ref
    Given I send a PUT request to "/api/v2/tickets/ref:{ticket1:ref}" with body:
    """
{
  "subject": "Modified 5"
}
    """
    And the response status code should be 204
    When I send a GET request to "/api/v2/tickets/ref:{ticket1:ref}"
    Then the response status code should be 200
    And the JSON node "data.subject" should be equal to "Modified 5"

  Scenario: I delete a ticket then verify it's properly soft-deleted
    Given I send a DELETE request to "/api/v2/tickets/{ticket1}"
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "hidden.{ts2}"

  Scenario: I delete a ticket by ref then verify it's properly soft-deleted
    Given I have a Ticket record referenced as ticket_for_del
    When I send a DELETE request to "/api/v2/tickets/ref:{ticket_for_del:ref}"
    Then I send a GET request to "/api/v2/tickets/ref:{ticket_for_del:ref}"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "hidden.{ts2}"

  Scenario: I retrieve a ticket
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "First Demo Ticket"
    And the JSON node "linked" should have 0 elements

  Scenario: I retrieve a ticket by ref
    When I send a GET request to "/api/v2/tickets/ref:{ticket1:ref}"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "First Demo Ticket"

  Scenario: I retrieve list of tickets
    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "linked" should have 0 elements

  Scenario: I retrieve list of tickets with count set
    When I send a GET request to "/api/v2/tickets?count=2"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "linked" should have 0 elements

  Scenario: I retrieve list of tickets with offset and count set
    When I send a GET request to "/api/v2/tickets?offset=1&count=2"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "linked" should have 0 elements
    And the JSON node "data[0].id" should be equal to "{ticket2}"
    And the JSON node "data[1].id" should be equal to "{ticket3}"

  Scenario: I retrieve list of hidden and archived tickets
    When I send a GET request to "/api/v2/tickets?status[]=hidden&status[]=archived"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "linked" should have 0 elements

  Scenario: I test filter by status
    When I send a GET request to "/api/v2/tickets?status[]=hidden.{ts2}"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to "{ticket5}"

    When I send a GET request to "/api/v2/tickets?status[]=hidden.{ts2}&status[]=hidden.{ts1}"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{ticket5}"
    And the JSON node "data[1].id" should be equal to "{ticket6}"

    When I send a GET request to "/api/v2/tickets?not_status[]=hidden"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements

    When I send a GET request to "/api/v2/tickets?status[]=awaiting_user&status[]=hidden.{ts2}"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{ticket1}"
    And the JSON node "data[1].id" should be equal to "{ticket5}"

  Scenario: I retrieve a ticket side loading people and organizations
    When I send a GET request to "/api/v2/tickets/{ticket1}?include=person,organization"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "First Demo Ticket"
    And the JSON node "linked" should have 2 elements
    And the JSON node "linked.person" should have 1 element
    And the JSON node "linked.person.{admin}.primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "linked.organization" should have 1 element
    And the JSON node "linked.organization.{microsoft}.name" should be equal to "Microsoft Corporation"

  Scenario: I retrieve list of tickets side loading people
    When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=desc&include=person"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].subject" should be equal to "Fourth Demo Ticket"
    And the JSON node "data[1].subject" should be equal to "Third Demo Ticket"
    And the JSON node "data[2].subject" should be equal to "Second Demo Ticket"
    And the JSON node "data[3].subject" should be equal to "First Demo Ticket"
    And the JSON node "linked" should have 1 element
    And the JSON node "linked.person" should have 2 elements
    And the JSON node "linked.person.{admin}.primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "linked.person.{agent}.primary_email" should be equal to "agent@deskpro.dev"

  Scenario: I try to create a ticket with incorrect user types
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~agent~,
  "agent": ~user~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.agent.errors[0].code" should be equal to "person_not_agent"
    And the JSON node "errors.fields.agent.errors[0].message" should exist

  Scenario: I try to add account email as cc
    Given I've just created a new person with name "Email account" and primary email "dev@deskprodev.com"
    Given I've just created a new email account "dev@deskprodev.com"
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "cc": ["dev@deskprodev.com"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].code" should be equal to "system_email"
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].message" should contain "dev@deskprodev.com"

  @skip-ci
  # This scenario passed because the api data set defined a require custom field
  Scenario: I try to create a ticket providing empty data
    When I send a POST request to "/api/v2/tickets"
    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I create a ticket with message
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~user~,
  "agent": ~agent~,
  "message": {
    "message": "my message"
  }
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{lastCreatedId}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].ticket" should be equal to "{lastCreatedId}"
    And the JSON node "data[0].message" should be equal to "my message"

  Scenario: I assign ticket author by email
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": "user@deskpro.dev",
  "agent": "agent@deskpro.dev",
  "message": {
    "message": "my message"
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to "{user}"
    And the JSON node "data.agent" should be equal to "{agent}"

  Scenario: I set custom ticket message author (different from ticket author)
    Given "user2@deskpro.dev" user exists
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": "user@deskpro.dev",
  "agent": "agent@deskpro.dev",
  "message": {
    "person": "user2@deskpro.dev",
    "message": "my message"
  }
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{lastCreatedId}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].person" should be equal to "{user2@deskpro.dev}"

  Scenario: I create a ticket with labels
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "labels": ["label1", "label2"]
}
    """
    Then the response status code should be 201
    And the JSON node "data.labels[0]" should be equal to "label1"
    And the JSON node "data.labels[1]" should be equal to "label2"

  Scenario: I create a ticket with messages within single request
    Given "user2@deskpro.dev" user exists
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "messages": [
    {
      "person": "user2@deskpro.dev",
      "message": "message 1"
    },
    {
      "message": "message 2"
    }
  ]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{lastCreatedId}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].person" should be equal to "{user2@deskpro.dev}"
    And the JSON node "data[0].message" should be equal to "message 1"
    And the JSON node "data[0].is_agent_note" should be equal to 0

    And the JSON node "data[1].person" should be equal to "{admin}"
    And the JSON node "data[1].message" should be equal to "message 2"
    And the JSON node "data[1].is_agent_note" should be equal to 0

  Scenario: I create a note and message
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "messages": [
    {
      "message": "message"
    },
    {
      "message": "note",
      "is_note": 1
    }
  ]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{lastCreatedId}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].person" should be equal to "{admin}"
    And the JSON node "data[0].message" should be equal to "message"
    And the JSON node "data[0].is_agent_note" should be equal to 0

    And the JSON node "data[1].person" should be equal to "{admin}"
    And the JSON node "data[1].message" should be equal to "note"
    And the JSON node "data[1].is_agent_note" should be equal to 1

  Scenario: I suppress user notify
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "suppress_user_notify": true
}
    """
    Then the response status code should be 201

  Scenario: I set create a ticket for specific brand
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "brand": ~b1~
}
    """
    Then the response status code should be 201
    And the JSON node "data.brand" should be equal to "{b1}"
    And the JSON node "data.department" should be equal to "{d1}"

  Scenario: I set create a ticket specify brand and department
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "brand": ~b2~,
  "department": ~d2~
}
    """
    Then the response status code should be 201
    And the JSON node "data.brand" should be equal to "{b2}"
    And the JSON node "data.department" should be equal to "{d2}"

  Scenario: I set create a ticket specify unrelated brand and department
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "brand": ~b1~,
  "department": ~d2~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should contain "bad_choice"

  Scenario: I create a ticket with specific dates
    # Note that date_status will be changed by TicketManager when it creates ticket
    Given "admin_for_tickets@deskpro.dev" admin exists
    And I'm authenticated as person with email "admin_for_tickets@deskpro.dev" with super key
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "date_created": "2018-06-20",
  "date_resolved": "2018-01-01 23:59:59",
  "date_archived": "2018-01-01 23:59:59",
  "date_feedback_rating": "2018-01-01 23:59:59",
  "date_first_agent_assign": "2018-01-01 23:59:59",
  "date_first_agent_reply": "2018-01-01 23:59:59",
  "date_last_agent_reply": "2018-01-01 23:59:59",
  "date_last_user_reply": "2018-01-01 23:59:59",
  "date_agent_waiting": "2018-01-01 23:59:59",
  "date_user_waiting": "2018-01-01 23:59:59",
  "date_on_hold": "2018-01-01 23:59:59",
  "date_locked": "2018-01-01 23:59:59",
  "total_user_waiting": 100,
  "total_to_first_reply": 200
}
    """
    Then the response status code should be 201
    And the JSON node "data.date_created" should be equal to "2018-06-20T00:00:00+0000"
    And the JSON node "data.date_resolved" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_archived" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_feedback_rating" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_first_agent_assign" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_first_agent_reply" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_last_agent_reply" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_last_user_reply" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_agent_waiting" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_on_hold" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_locked" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.total_user_waiting" should be equal to 100
    And the JSON node "data.total_to_first_reply" should be equal to 200

  Scenario: I create a ticket with specific date_first_agent_assign and date_on_hold, this should not be an error
    Given "admin_for_tickets@deskpro.dev" admin exists
    And I'm authenticated as person with email "admin_for_tickets@deskpro.dev" with super key
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "date_first_agent_assign": "2018-01-01 23:59:59",
  "date_on_hold": "2018-01-01 23:59:59"
}
    """
    Then the response status code should be 201
    And the JSON node "data.date_first_agent_assign" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_on_hold" should be equal to "2018-01-01T23:59:59+0000"

  Scenario: I create a ticket with specific date_first_agent_assign and date_on_hold, this should not be an error
    Given "admin_for_tickets@deskpro.dev" admin exists
    And I'm authenticated as person with email "admin_for_tickets@deskpro.dev" with super key
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person": ~admin~,
  "date_first_agent_assign": "2018-01-01 23:59:59",
  "date_on_hold": "2018-01-01 23:59:59"
}
    """
    Then the response status code should be 201
    And the JSON node "data.date_first_agent_assign" should be equal to "2018-01-01T23:59:59+0000"
    And the JSON node "data.date_on_hold" should be equal to "2018-01-01T23:59:59+0000"

  Scenario: I create a ticket with specific date_first_agent_assign and date_on_hold, this should not be an error2
    Given "admin_for_tickets@deskpro.dev" admin exists
    And I'm authenticated as person with email "admin_for_tickets@deskpro.dev" with super key
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "person": ~admin~,
  "subject": "Testing ticket created with message and date_created is not causing error",
  "message": {
    "person": ~admin~,
    "message": "Some message",
    "format": "text"
  },
  "cc": [],
  "status": "awaiting_agent",
  "date_created": "2018-01-01 23:59:59"
}
    """
    Then the response status code should be 201
    And the JSON node "data.date_created" should be equal to "2018-01-01T23:59:59+0000"

    When I send a GET request to "/api/v2/tickets/{lastCreatedId}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].ticket" should be equal to "{lastCreatedId}"
    And the JSON node "data[0].message" should be equal to "Some message"
    And the JSON node "data[0].date_created" should not be null
