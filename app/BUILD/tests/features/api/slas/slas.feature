@new
Feature: /slas endpoint
  To retrieve DeskPRO SLAs
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And only the following SLA records exist:
    | #  | SLA type | Title  |
    | s1 | warning  | First  |
    | s2 | test     | Second |
    | s3 | watning  | Third  |

  Scenario: I get paginated list of SLAs
    When I send a GET request to "/api/v2/slas"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].apply_terms" should not exist
    And the JSON node "data[0].warn_actions" should not exist
    And the JSON node "data[0].fail_actions" should not exist

  Scenario: I get a single SLA
    When I send a GET request to "/api/v2/slas/{s1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "First"
    And the JSON node "data.apply_terms" should exist
    And the JSON node "data.warn_actions" should exist
    And the JSON node "data.fail_actions" should exist
