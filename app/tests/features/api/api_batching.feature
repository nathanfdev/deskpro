Feature: JSON Pagination
  In order to page through collection responses
  As a developer
  I need meta data to tell me about the pagination info

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
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
            "an_identifier": "/api/v2/sandbox_widgets"
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
                "url": "/api/v2/sandbox_widgets",
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
                "url": "/api/v2/sandbox_widgets",
                "headers": {
                    "authorize": "key se3LaKeY5"
                },
                "data": {
                    "name": "some name here!",
                    "inventory": 11
                }
            },
            "an_identifier": "/api/v2/sandbox_widgets"
        }
    }
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "responses.an_identifier" should exist
    And the JSON node "responses.new_stuff" should exist
    And the JSON node "responses.an_identifier.headers.status-code" should be equal to 200
    And the JSON node "responses.new_stuff.headers.status-code" should be equal to 201

