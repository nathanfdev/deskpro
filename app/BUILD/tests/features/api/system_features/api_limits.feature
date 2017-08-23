Feature: API limits
  Each key limited with global and individual limit (if set)

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I'm getting standard API endpoint
    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 200

  Scenario: I'm getting API endpoint
    Given my key limit almost exhausted
    When I send a GET request to "/api/v2/user_groups"
    Then the response should be in JSON
    And the response status code should be 200
    When I send a GET request to "/api/v2/user_groups"
    Then the response status code should be 403
    #there is no api with disabled limits now
    #But I send a GET request to "/api/v2/tickets"
    #And the response status code should be 200

  Scenario: My key limit replenished
   Given my key limit will be replenished
   When I send a GET request to "/api/v2/user_groups"
   Then the response status code should be 200
   And the response should be in JSON

  Scenario: My key limit is normal but global limits are exhausted
    Given global limits are exhausted
    When I send a GET request to "/api/v2/user_groups"
    Then the response status code should be 403
    And the response should be in JSON
    And the JSON node "status" should be equal to "403"
    And the JSON node "code" should be equal to "rate_limits"
    And the JSON node "message" should be equal to "Your limit for api calls is exhausted."
