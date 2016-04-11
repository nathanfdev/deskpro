@basic @tickets
Feature: /tickets endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a ticket w/o sideloading
    When I send a GET request to "/api/v2/tickets/1"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "Test"
    And the JSON node "linked" should have 0 elements

  Scenario: I retrieve a ticket with sideloading
    When I send a GET request to "/api/v2/tickets/2?include=person,organization"
    And the response status code should be 200
    And the JSON node "data.subject" should be equal to "Ticket #1"
    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.person.1.primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "linked.person.3.id" should be equal to 3
    And the JSON node "linked.person.3.primary_email" should be equal to "user@deskpro.dev"
    And the JSON node "linked.organization.1.id" should be equal to 1
    And the JSON node "linked.organization.1.name" should be equal to "Organization 1"
    And the JSON node "linked.organization.2.id" should be equal to 2
    And the JSON node "linked.organization.2.name" should be equal to "Organization 2"

  Scenario: I retrieve list of tickets
    When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=desc&include=person"
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].subject" should be equal to "Ticket #3"
    And the JSON node "data[1].subject" should be equal to "Ticket #2"
    And the JSON node "data[2].subject" should be equal to "Ticket #1"
    And the JSON node "data[3].subject" should be equal to "Test"
    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.person.1.primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "linked.person.2.id" should be equal to 2
    And the JSON node "linked.person.2.primary_email" should be equal to "agent@deskpro.dev"
    And the JSON node "linked.person.3.id" should be equal to 3
    And the JSON node "linked.person.3.primary_email" should be equal to "user@deskpro.dev"

  Scenario: I retrieve list of tickets w/o sideloading
    When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=desc"
    And the JSON node "data" should have 4 elements
    And the JSON node "linked.person" should not exist

  Scenario: I try to create a ticket providing empty data
    When I send a POST request to "/api/v2/tickets"
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.subject" should be equal to "(No Subject)"

  Scenario: I try to create a ticket with not correct user types
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "person":  1,
  "agent": 3
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.agent.errors[0].code" should be equal to "person_not_agent"
    And the JSON node "errors.fields.agent.errors[0].message" should contain "is not agent."

  Scenario: I create a ticket
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Sample Ticket",
  "department": 1,
  "is_hold": true,
  "person":  3,
  "agent": 1,
  "followers": ["agent@deskpro.dev", 1],
  "cc": ["user@deskpro.dev"],
  "fields": {
    "6": "some text"
  }
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/tickets/6"
    And the JSON node "data.id" should be equal to 6
    And the JSON node "data.subject" should be equal to "Sample Ticket"
    And the JSON node "data.is_hold" should be equal to 1
    And the JSON node "data.person" should be equal to 3
    And the JSON node "data.agent" should be equal to 1
    And the JSON node "data.cc" should have 1 element
    And the JSON node "data.cc[0]" should be equal to 3
    And the JSON node "data.followers" should have 2 elements
    And the JSON node "data.followers[0]" should be equal to 2
    And the JSON node "data.followers[1]" should be equal to 1
    And the JSON node "data.fields.6.value" should be equal to "some text"

    When I send a GET request to "/api/v2/tickets/6"
    Then the response status code should be 200
    And the JSON node "data.subject" should be equal to "Sample Ticket"
    And the JSON node "data.cc" should have 1 element
    And the JSON node "data.cc[0]" should be equal to 3
    And the JSON node "data.followers" should have 2 elements
    And the JSON node "data.followers[0]" should be equal to 2
    And the JSON node "data.followers[1]" should be equal to 1

  Scenario: I modify and retrieve a ticket
    When I send a PUT request to "/api/v2/tickets/6" with body:
    """
{
  "subject": "Modified subject",
  "followers": [1, 4]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/6"
    Then the response status code should be 200
    And the JSON node "data.subject" should be equal to "Modified subject"
    And the JSON node "data.cc" should have 1 element
    And the JSON node "data.cc[0]" should be equal to 3
    And the JSON node "data.followers" should have 2 elements
    And the JSON node "data.followers[0]" should be equal to 1
    And the JSON node "data.followers[1]" should be equal to 4

  Scenario: I delete a ticket
    When I send a DELETE request to "/api/v2/tickets/5"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I try to get deleted ticket
    When I send a GET request to "/api/v2/tickets/5"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "deleted"

  Scenario: I try to get not existing ticket
    When I send a GET request to "/api/v2/tickets/40404"
    Then the response status code should be 404

  Scenario: I try to modify not existing ticket
    When I send a PUT request to "/api/v2/tickets/40404" with body:
    """
{
  "subject": "Modified subject"
}
    """
    Then the response status code should be 404
