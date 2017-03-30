@new
Feature: CRUD offset

  Background:
    Given there are no "Person" records
    And I'm authenticated as agent
    And the following User records exist:
      | #   | Name    |
      | u1  | User 1  |
      | u2  | User 2  |
      | u3  | User 3  |
      | u4  | User 4  |
      | u5  | User 5  |
      | u6  | User 6  |
      | u7  | User 7  |
      | u8  | User 8  |
      | u9  | User 9  |
      | u10 | User 10 |

  Scenario: I get list w/o offset
    When I send a GET request to "/api/v2/people?&order_dir=asc"
    Then the JSON node "data" should have 10 elements
    And the JSON node "meta.per_page" should not exist
    And the JSON node "meta.total" should not exist

  Scenario: I get list w/ offset and default count
    When I send a GET request to "/api/v2/people?&order_dir=asc&offset=5"
    Then the JSON node "data" should have 6 elements
    And the JSON node "data[0].id" should be equal to "{u5}"
    And the JSON node "meta.per_page" should be equal to 10
    And the JSON node "meta.total" should be equal to 11

  Scenario: I get list w/ offset and count
    When I send a GET request to "/api/v2/people?&order_dir=asc&offset=5&count=2"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{u5}"
    And the JSON node "data[1].id" should be equal to "{u6}"
    And the JSON node "meta.per_page" should be equal to 2
    And the JSON node "meta.total" should be equal to 11
