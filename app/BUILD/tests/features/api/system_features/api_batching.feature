@new
Feature: API batch requests
  In order to improve app performance
  As an API user
  I need ability to perform batch API requests

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title               | Brands           | Is Tickets Enabled | Is Chat Enabled |
      | d1 | Ticket Department 1 | [{defaultBrand}] | 1                  | 0               |
      | d2 | Ticket Department 2 | [{defaultBrand}] | 1                  | 0               |
    And only the following Ticket records exist:
      | #  | Subject  | Person  | Department |
      | t1 | Ticket 1 | {admin} | {d1}       |
    And only the following Organization records exist:
      | #  | Name  |
      | o1 | Org 1 |
      | o2 | Org 2 |

  Scenario: Invalid input because I do not include the "requests" node
    When I send a POST request to "/api/v2/batch" with body:
    """
{
  "invalid": "must include the requests node"
}
    """
    Then the response status code should be 400

  Scenario: I use batch to make a single GET request using only a string url
    When I send a POST request to "/api/v2/batch" with body:
    """
{
  "requests": {
    "an_identifier": "/api/v2/user_groups"
  }
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "responses.an_identifier" should exist
    And the JSON node "responses.an_identifier.data" should exist
    And the JSON node "responses.an_identifier.meta" should exist
    And the JSON node "responses.an_identifier.headers" should exist

  Scenario: I use batch to make a single GET request using the verbose method
    When I send a POST request to "/api/v2/batch" with body:
    """
{
  "requests": {
    "an_identifier": {
      "method": "GET",
      "url": "/api/v2/user_groups",
      "headers": {
        "authorize": "key se3LaKeY5"
      }
    }
  }
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "responses.an_identifier" should exist
    And the JSON node "responses.an_identifier.data" should exist
    And the JSON node "responses.an_identifier.meta" should exist
    And the JSON node "responses.an_identifier.headers" should exist

  Scenario: I use batch to make a POST request (with data) and a GET request
    When I send a POST request to "/api/v2/batch" with body:
    """
{
  "requests": {
    "new_stuff": {
      "method": "POST",
      "url": "/api/v2/people",
      "headers": {
        "authorize": "key se3LaKeY5"
      },
      "data": {
        "name": "New User",
        "primary_email": "new@deskpro.dev"
      }
    },
    "an_identifier": "/api/v2/user_groups"
  }
}
    """
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "responses.an_identifier" should exist
    And the JSON node "responses.new_stuff" should exist
    And the JSON node "responses.an_identifier.headers.status-code" should be equal to 200
    And the JSON node "responses.new_stuff.headers.status-code" should be equal to 201

  Scenario: I perform batch requests via GET providing comma separated list of requests
    When I send a GET request to "/api/v2/batch?get=/api/v2/ticket_stars,/api/v2/ticket_departments,/api/v2/organizations/counts,/tickets"
    Then the response status code should be 200
    And the JSON node "responses" should have 4 elements
    And the JSON node "responses[0].data[0].color" should exist
    And the JSON node "responses[1].data[0].title" should exist
    And the JSON node "responses[2].data.count" should exist
    And the JSON node "responses[3].data[0].subject" should exist

  Scenario: I perform batch requests via GET providing string request identifiers
    When I send a GET request to "/api/v2/batch?get[stars]=/api/v2/ticket_stars&get[departments]=/api/v2/ticket_departments&get[counts]=/api/v2/organizations/counts"
    Then the response status code should be 200
    And the JSON node "responses" should have 3 elements
    And the JSON node "responses.stars.data[0].color" should exist
    And the JSON node "responses.departments.data[0].title" should exist
    And the JSON node "responses.counts.data.count" should exist

  Scenario: I perform batch requests via GET providing extended array request specs
    When I send a GET request to "/api/v2/batch?get[stars]=/api/v2/ticket_stars&get[organizations][url]=/api/v2/organizations&get[organizations][params][count]=1&get[organizations][params][page]=2&get[counts]=/api/v2/organizations/counts"
    Then the response status code should be 200
    And the JSON node "responses" should have 3 elements
    And the JSON node "responses.stars.data[0].color" should exist
    And the JSON node "responses.organizations.data[0].name" should exist
    And the JSON node "responses.organizations.data" should have 1 element
    And the JSON node "responses.organizations.meta.pagination.current_page" should be equal to 2
    And the JSON node "responses.counts.data.count" should exist

  Scenario: I perform batch requests with sideloading
    When I send a GET request to "/api/v2/batch?get[tickets]=/tickets?include=person"
    Then the response status code should be 200
    And the JSON node "responses.tickets.linked.person.{admin}.id" should be equal to "{admin}"
