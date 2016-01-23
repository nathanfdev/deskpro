Feature: Quick Search

  Background:
    Given I install the api data set
    And my request is authenticated

  # Doctrine search adapter
  @reinstall
  Scenario: I send empty query request without "agent_people.use" permission
    Given I set permission "agent_people.use" = 0 for "registered" usergroup
    Given the setting "elastica.enabled" is set to 0
    When I send a GET request to "/api/v2/search"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "grouped_results[0].type" should be equal to "article"
    And the JSON node "grouped_results[1].type" should be equal to "download"
    And the JSON node "grouped_results[2].type" should be equal to "feedback"
    And the JSON node "grouped_results[3].type" should be equal to "news"
    And the JSON node "grouped_results[4].type" should be equal to "ticket"
    And the JSON node "grouped_results[5].type" should be equal to "chat_conversation"

  Scenario: I send empty query request with "agent_people.use" permission
    Given I set permission "agent_people.use" = 1 for "registered" usergroup
    Given the setting "elastica.enabled" is set to 0
    When I send a GET request to "/api/v2/search"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "grouped_results[0].type" should be equal to "article"
    And the JSON node "grouped_results[0].results" should have 0 elements
    And the JSON node "grouped_results[1].type" should be equal to "download"
    And the JSON node "grouped_results[1].results" should have 0 elements
    And the JSON node "grouped_results[2].type" should be equal to "feedback"
    And the JSON node "grouped_results[2].results" should have 0 elements
    And the JSON node "grouped_results[3].type" should be equal to "news"
    And the JSON node "grouped_results[3].results" should have 0 elements
    And the JSON node "grouped_results[4].type" should be equal to "ticket"
    And the JSON node "grouped_results[4].results" should have 0 elements
    And the JSON node "grouped_results[5].type" should be equal to "person"
    And the JSON node "grouped_results[5].results" should have 0 elements
    And the JSON node "grouped_results[6].type" should be equal to "organization"
    And the JSON node "grouped_results[6].results" should have 0 elements
    And the JSON node "grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "grouped_results[7].results" should have 0 elements

  Scenario: I search by id
    Given I set permission "agent_people.use" = 1 for "registered" usergroup
    Given the setting "elastica.enabled" is set to 0
    When I send a GET request to "/api/v2/search?q=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "grouped_results[0].type" should be equal to "article"
    And the JSON node "grouped_results[0].results" should have 1 elements

