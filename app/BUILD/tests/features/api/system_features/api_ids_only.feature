Feature: JSON API fetch ids only

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I search with ids only
    When I send a GET request to "/api/v2/search?q=1&ids_only=1"
    Then the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results[0]" should be equal to 1
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results[0]" should be equal to 1
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results[0]" should be equal to 1
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results[0]" should be equal to 1
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results[0]" should be equal to 1
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results[0]" should be equal to 1
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results[0]" should be equal to 1
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results[0]" should be equal to 1

  Scenario Outline: I get lists of data
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/<endpoint>?order_by=id&order_dir=asc&ids_only=1"
    Then the JSON node "data" should have <count> element
    And the JSON node "data[0]" should be equal to <id1>
    And the JSON node "data[1]" should be equal to <id2>

    Examples:
      | endpoint                 | count | id1 | id2 |
      | people                   | 4     | 1   | 2   |
      | tickets                  | 4     | 1   | 2   |
      | ticket_filters/1/tickets | 2     | 2   | 4   |
