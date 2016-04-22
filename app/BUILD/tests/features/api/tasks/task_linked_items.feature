@tasks
Feature: /tasks/{id}/linked_items/(articles|chats|tickets) endpoints
  To CRUD DeskPRO task linked items
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario Outline: I try to create item with malformed request
    When I send a POST request to "/api/v2/tasks/1/linked_items/<type>"
    Then the response status code should be 400
    And the JSON node "errors.fields.item.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.item.errors[0].message" should contain "This value should not be blank."

    Examples:
      | type     |
      | articles |
      | chats    |
      | tickets  |

  Scenario Outline: I check validation
    When I send a POST request to "/api/v2/tasks/1/linked_items/<type>" with body:
    """
{
  "item": 1,
  "unknown": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: unknown"

    Examples:
    | type     |
    | articles |
    | chats    |
    | tickets  |

  Scenario Outline: I create linked items
    When I send a POST request to "/api/v2/tasks/1/linked_items/<type>" with body:
    """
{
  "item": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.<param>" should be equal to "<value1>"

    When I send a POST request to "/api/v2/tasks/1/linked_items/<type>" with body:
    """
{
  "item": 2
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.<param>" should be equal to "<value2>"

    Examples:
      | type     | param   | value1         | value2           |
      | articles | title   | A test article | Test Article #2  |
      | chats    | subject | Test chat 1    | Test chat 2      |
      | tickets  | subject | Test           | Ticket #1        |

  Scenario Outline: I try to create a linked item with the same id
    When I send a POST request to "/api/v2/tasks/1/linked_items/<type>" with body:
    """
{
  "item": 2
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.item.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.item.errors[0].message" should contain "This value already exists in the system."

    Examples:
      | type     |
      | articles |
      | chats    |
      | tickets  |

  Scenario Outline: I retrieve a list of linked items
    When I send a GET request to "/api/v2/tasks/1/linked_items/<type>"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 2
    And the JSON node "data[0].<param>" should be equal to "<value2>"

    And the JSON node "data[1].id" should be equal to 1
    And the JSON node "data[1].<param>" should be equal to "<value1>"

    Examples:
      | type     | param   | value1         | value2           |
      | articles | title   | A test article | Test Article #2  |
      | chats    | subject | Test chat 1    | Test chat 2      |
      | tickets  | subject | Test           | Ticket #1        |

  Scenario Outline: I get single linked item
    When I send a GET request to "/api/v2/tasks/1/linked_items/<type>/2?include=person"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.<param>" should be equal to "<value>"
    And the JSON node "linked.person.1.primary_email" should be equal to "admin@deskpro.dev"

    Examples:
      | type     | param   | value            |
      | articles | title   | Test Article #2  |
      | chats    | subject | Test chat 2      |
      | tickets  | subject | Ticket #1        |

  Scenario Outline: I delete a linked item
    When I send a DELETE request to "/api/v2/tasks/1/linked_items/<type>/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tasks/1/linked_items/<type>/1"
    Then the response status code should be 404

    Examples:
      | type     |
      | articles |
      | chats    |
      | tickets  |
