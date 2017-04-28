Feature: /mass_actions endpoint
  To complete mass actions on item's lists
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I send POST request for non-existent type of content
    When I send a POST request to "/api/v2/mass_actions/something" with body:
    """
{
  "ids": [1],
  "params":{"set_status":1}
}
    """
    Then the response status code should be 404

  Scenario: I try to apply non-existed action on tickets
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"non_existed":"anything"}
}
    """
    Then the response status code should be 400

  Scenario: I post mass actions request with empty ids
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [],
  "params":{"set_status":"awaiting_agent"}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.ids.errors[0].code" should be equal to "too_few_elements"

  Scenario: I post mass actions request without ids
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "params":{"set_status":"awaiting_agent"}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.ids.errors[0].code" should be equal to "too_few_elements"
