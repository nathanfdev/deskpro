@new
Feature: /tickets endpoint
  To CRUD DeskPRO tickets
  As an API user
  I want an API endpoint

  Background:
    Given no Person records exist
    And no EmailAccount records exist
    And I'm authenticated as admin
    And agent and user exist
    And only the following Organization records exist:
      | #         | Name                  |
      | microsoft | Microsoft Corporation |
    And only the following Ticket records exist:
      | #       | Subject            | Agent   | Organization | Status         | Hidden status |
      | ticket1 | First Demo Ticket  | {admin} | {microsoft}  | awaiting_user  |               |
      | ticket2 | Second Demo Ticket | {agent} |              | awaiting_agent |               |
      | ticket3 | Third Demo Ticket  | {agent} |              | resolved       |               |
      | ticket4 | Fourth Demo Ticket | {agent} |              | archived       |               |
      | ticket5 | Fifth Demo Ticket  | {agent} |              | hidden         | deleted       |
    And I have a Department record referenced as department
    And there are no custom ticket fields defined

  Scenario: I create a ticket
    Given only the following Product records exist:
      | #  | Title     |
      | p1 | Product 1 |
      | p2 | Product 2 |
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket",
  "parent": ~ticket1~,
  "department": ~department~,
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
    And the JSON node "data.department" should be equal to "{department}"
    And the JSON node "data.product" should be equal to "{p2}"
    And the JSON node "data.person" should be equal to "{user}"
    And the JSON node "data.agent" should be equal to "{agent}"
    And the JSON node "data.cc" should have 3 elements
    And the JSON node "data.cc[0]" should be equal to "{user}"
    And the JSON node "data.cc[1]" should be equal to "{agent}"
    And the JSON node "data.cc[2]" should be equal to "{admin}"
    And the JSON node "data.star" should be equal to the string "green"

  Scenario: I modify a ticket
    When I send a PUT request to "/api/v2/tickets/{ticket1}" with body:
    """
{
  "subject": "Modified 2"
}
    """
    Then the response status code should be 204

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
    And the JSON node "data.status" should be equal to "hidden.deleted"

  Scenario: I delete a ticket by ref then verify it's properly soft-deleted
    Given I have a Ticket record referenced as ticket_for_del
    When I send a DELETE request to "/api/v2/tickets/ref:{ticket_for_del:ref}"
    Then I send a GET request to "/api/v2/tickets/ref:{ticket_for_del:ref}"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "hidden.deleted"

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
    And the JSON node "data" should have 2 elements
    And the JSON node "linked" should have 0 elements

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
