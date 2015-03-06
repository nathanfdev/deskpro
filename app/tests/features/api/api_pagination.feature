Feature: JSON Pagination
  In order to page through collection responses
  As a developer
  I need meta data to tell me about the pagination info

  Background:
    Given I install the api data set

  @reinstall
  Scenario: I make a request that is paginated (resource collections)
    Given there are 31 sandbox widgets
    When I send a GET request to "/api/v2/sandbox_widgets"
    Then the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to "10"
    And the JSON node "meta.total_count" should be equal to "31"
    And the JSON node "meta.page" should be equal to "1"
    And the JSON node "meta.total_pages" should be equal to "4"

  @reinstall
  Scenario: I make a resource collection request with 0 results
    When I send a GET request to "/api/v2/sandbox_widgets"
    Then the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to "0"
    And the JSON node "meta.total_count" should be equal to "0"
    And the JSON node "meta.page" should be equal to "1"
    And the JSON node "meta.total_pages" should be equal to "1"

