Feature: API batch requests
  In order to improve app performance
  As an API user
  I need ability to perform batch API requests

  Background:
    Given I install the api data set
    And my request is authenticated

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
                "url": "/api/v2/tickets",
                "headers": {
                    "authorize": "key se3LaKeY5"
                },
                "data": {
                    "subject": "My ticket",
                    "fields": {
                      "6": "some custom text"
                    }
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
    When I send a GET request to "/api/v2/batch?get=/api/v2/ticket_stars,/api/v2/departments,/api/v2/organizations/counts,/tickets"
    Then the response status code should be 200
    And the JSON node "responses" should have 4 elements
    And the JSON node "responses[0].data[0].color" should exist
    And the JSON node "responses[1].data[0].title" should exist
    And the JSON node "responses[2].data.count" should exist
    And the JSON node "responses[3].data[0].subject" should exist

  Scenario: I perform batch requests via GET providing string request identifiers
    When I send a GET request to "/api/v2/batch?get[stars]=/api/v2/ticket_stars&get[departments]=/api/v2/departments&get[counts]=/api/v2/organizations/counts"
    Then the response status code should be 200
    And the JSON node "responses" should have 3 elements
    And the JSON node "responses.stars.data[0].color" should exist
    And the JSON node "responses.departments.data[0].title" should exist
    And the JSON node "responses.counts.data.count" should exist

  Scenario: I perform batch requests via GET providing extended array request specs
    When I send a GET request to "/api/v2/batch?get[stars]=/api/v2/ticket_stars&get[departments][url]=/api/v2/departments&get[departments][params][count]=1&get[departments][params][page]=2&get[counts]=/api/v2/organizations/counts"
    Then the response status code should be 200
    And the JSON node "responses" should have 3 elements
    And the JSON node "responses.stars.data[0].color" should exist
    And the JSON node "responses.departments.data[0].title" should exist
    And the JSON node "responses.departments.data" should have 1 element
    And the JSON node "responses.departments.meta.pagination.current_page" should be equal to 2
    And the JSON node "responses.counts.data.count" should exist

  Scenario: I perform batch requests with sideloading
    When I send a GET request to "/api/v2/batch?get[tickets]=/tickets?include=person"
    Then the response status code should be 200
    And the JSON node "responses.tickets.linked.person.1.id" should be equal to 1
