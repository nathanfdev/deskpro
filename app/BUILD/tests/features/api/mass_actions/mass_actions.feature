Feature: /mass_actions endpoint
  To complete mass actions on item's lists
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And only the following Ticket records exist:
      | #  | Subject  | Department | Date Created        |
      | t1 | Ticket 1 | {d1}       | 2020-03-05 00:00:00 |
      | t2 | Ticket 2 | {d1}       | 2020-03-06 00:00:00 |

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

  Scenario: I post mass actions with date_created and no ids
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "date_created": {
    "min": "2020-03-04",
    "max": "2020-03-10"
  },
  "params":{"set_status":"awaiting_agent"}
}
    """
    Then the response status code should be 204
