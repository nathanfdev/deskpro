@new
Feature: /user_sources endpoint
  To retrieve DeskPRO user sources
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario Outline: I get list of user sources
    When I send a GET request to "/api/v2/user_sources/<context>"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].source_type" should be equal to "deskpro"
    And the JSON node "data[0].display_options" should have 0 elements
    And the JSON node "data[0].id" should exist
    And the JSON node "data[0].title" should be equal to "DeskPRO"
    And the JSON node "data[0].type" should be equal to <context>
    And the JSON node "data[0].display_order" should be equal to "-10"
    And the JSON node "data[0].is_enabled" should be equal to 1

    And the JSON node "data[1].source_type" should be equal to "callbackadaptermock"
    And the JSON node "data[1].display_options" should have 1 element
    And the JSON node "data[1].display_options.login_url" should contain "login?format=default"
    And the JSON node "data[1].id" should exist
    And the JSON node "data[1].title" should be equal to "GooglePlus"
    And the JSON node "data[1].type" should be equal to <context>
    And the JSON node "data[1].display_order" should be equal to "0"
    And the JSON node "data[1].is_enabled" should be equal to 1

    Examples:
      | context |
      | agent   |
      | user    |

  Scenario Outline: I filter list of user sources
    When I send a GET request to "/api/v2/user_sources/<context>?is_enabled=<is_enabled>"
    Then the JSON node "data" should have <count> elements

    Examples:
      | context | is_enabled | count |
      | agent   | 0          | 0     |
      | agent   | 1          | 2     |
      | user    | 0          | 1     |
      | user    | 1          | 2     |
