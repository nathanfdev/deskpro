Feature: /tickets/{id}/messages endpoint
  To CRUD DeskPRO ticket messages
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a ticket messages
    When I send a GET request to "/api/v2/tickets/1/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/tickets/1/messages/1"
    Then the response status code should be 404

  Scenario: Scenario: I fail form validation
    When I send a POST request to "/api/v2/tickets/1/messages"
    Then the response status code should be 400
    And the JSON node "errors.fields.message.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I add ticket messages
    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "my message"
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.ticket" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should be equal to "my message"

    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<span>my html message</span>"
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.ticket" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should be equal to "<span>my html message</span>"

    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<span>my html message</span>",
      "format": "text"
    }
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.ticket" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 0
    And the JSON node "data.message" should contain '&lt;span&gt;my html message&lt;'

    When I send a POST request to "/api/v2/tickets/1/messages" with body:
    """
    {
      "message": "<span>my note</span>",
      "format": "html",
      "is_note": true
    }
    """
    And the JSON node "data.id" should be equal to 4
    And the JSON node "data.ticket" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.is_agent_note" should be equal to 1
    And the JSON node "data.message" should contain '<span>my note</span>'

  Scenario: I retrieve a ticket messages after adding
    When I send a GET request to "/api/v2/tickets/1/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 4 element
    And the JSON node "data[0].message" should contain "my message"
    And the JSON node "data[1].message" should contain "my html message"
    And the JSON node "data[3].message" should contain "my note"

    When I send a GET request to "/api/v2/tickets/1/messages/1"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "my message"

    When I send a GET request to "/api/v2/tickets/1/messages/4"
    Then the response status code should be 200
    And the JSON node "data.message" should contain "my note"
