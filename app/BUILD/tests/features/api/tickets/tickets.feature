@new
Feature: /tickets endpoint
  To CRUD DeskPRO tickets
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And agent@deskpro.com and user@deskpro.com exist
    And the following Organization records exist:
      | #         | Name                  |
      | microsoft | Microsoft Corporation |
    And only the following Ticket records exist:
      | #         | Subject               | Agent   | Organization |
      | ticket1   | First Demo Ticket     | {admin} | {microsoft}  |
      | ticket2   | Second Demo Ticket    | {agent} |              |
      | ticket3   | Third Demo Ticket     | {agent} |              |
    And I have a Department record referenced as department
    And there are no custom ticket fields defined

  Scenario: I create a ticket
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket",
  "parent": ~ticket1~,
  "department": ~department~,
  "is_hold": true,
  "person":  ~user~,
  "agent": ~agent~,
  "followers": ["~agent:primary_email~", ~admin:id~],
  "cc": ["~user:primary_email~"]
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/tickets/{lastCreatedId}"
    And the JSON node "data.subject" should be equal to "Test Ticket"
    And the JSON node "data.is_hold" should be equal to 1
    And the JSON node "data.parent" should be equal to "{ticket1}"
    And the JSON node "data.department" should be equal to "{department}"
    And the JSON node "data.person" should be equal to "{user}"
    And the JSON node "data.agent" should be equal to "{agent}"
    And the JSON node "data.cc" should have 1 element
    And the JSON node "data.cc[0]" should be equal to "{user}"
    And the JSON node "data.followers" should have 2 elements
    And the JSON node "data.followers[0]" should be equal to "{agent}"
    And the JSON node "data.followers[1]" should be equal to "{admin}"

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
  "followers": [~agent~, ~admin~]
}
    """
    And the response status code should be 204
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    Then the response status code should be 200
    And the JSON node "data.subject" should be equal to "Modified 4"
    And the JSON node "data.followers" should have 2 elements

  Scenario: I delete a ticket then verify it's properly soft-deleted
    Given I send a DELETE request to "/api/v2/tickets/{ticket1}"
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "deleted"

  Scenario: I retrieve a ticket
    When I send a GET request to "/api/v2/tickets/{ticket1}"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "First Demo Ticket"
    And the JSON node "linked" should have 0 elements

  Scenario: I retrieve list of tickets
    When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=desc"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "linked" should have 0 elements

  Scenario: I retrieve a ticket side loading people and organizations
    When I send a GET request to "/api/v2/tickets/{ticket1}?include=person,organization"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "First Demo Ticket"
    And the JSON node "linked" should have 2 elements
    And the JSON node "linked.person" should have 1 element
    And the JSON node "linked.person.{admin}.primary_email" should be equal to "admin@deskpro.com"
    And the JSON node "linked.organization" should have 1 element
    And the JSON node "linked.organization.{microsoft}.name" should be equal to "Microsoft Corporation"

  Scenario: I retrieve list of tickets side loading people
    When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=desc&include=person"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].subject" should be equal to "Third Demo Ticket"
    And the JSON node "data[1].subject" should be equal to "Second Demo Ticket"
    And the JSON node "data[2].subject" should be equal to "First Demo Ticket"
    And the JSON node "linked" should have 1 element
    And the JSON node "linked.person" should have 2 elements
    And the JSON node "linked.person.{admin}.primary_email" should be equal to "admin@deskpro.com"
    And the JSON node "linked.person.{agent}.primary_email" should be equal to "agent@deskpro.com"

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

  Scenario: I trye to add account email as cc
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
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].message" should contain "is already being used as email account."

  @skip-ci
  # This scenario passed because the api data set defined a require custom field
  Scenario: I try to create a ticket providing empty data
    When I send a POST request to "/api/v2/tickets"
    Then the response should be in JSON
    And the response status code should be 400
