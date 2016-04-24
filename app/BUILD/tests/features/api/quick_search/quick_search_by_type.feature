Feature: Quick Search
  I want to check search by entity type

  Background:
    Given I install the api data set
    And my request is authenticated
    And the setting "elastica.enabled" is set to 0

  Scenario: I search by specific types
    When I send a GET request to "/api/v2/search?q=1&types=article,ticket,news"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results" should have 3 elements
    And the JSON node "data.grouped_results[0].results[0].id" should be equal to 1
    And the JSON node "data.grouped_results[0].results[0].title" should be equal to "A test article"
    And the JSON node "data.grouped_results[1].results[0].id" should be equal to 1
    And the JSON node "data.grouped_results[1].results[0].subject" should be equal to "Test"
    And the JSON node "data.grouped_results[2].results[0].id" should be equal to 1
    And the JSON node "data.grouped_results[2].results[0].title" should be equal to "Test News #1"

  Scenario: I search by people and orgs
    When I send a GET request to "/api/v2/search/people_and_orgs?q=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results" should have 2 elements
    And the JSON node "data.grouped_results[0].results[0].id" should be equal to 1
    And the JSON node "data.grouped_results[0].results[0].primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "data.grouped_results[1].results[0].id" should be equal to 1
    And the JSON node "data.grouped_results[1].results[0].name" should be equal to "Organization 1"

  Scenario Outline: I search by entity type
    When I send a GET request to "/api/v2/search/<type>?q=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].<param>" should be equal to "<value>"

    Examples:
      | type              | param         | value             |
      | article           | title         | A test article    |
      | download          | title         | Test Download #1  |
      | feedback          | title         | Test feedback 1   |
      | news              | title         | Test News #1      |
      | ticket            | subject       | Test              |
      | person            | primary_email | admin@deskpro.dev |
      | organization      | name          | Organization 1    |
      | chat_conversation | subject       | Test chat 1       |

  Scenario: I try to search by unsupported type
    When I send a GET request to "/api/v2/search/unknown?q=1"
    Then the response status code should be 404

    When I send a GET request to "/api/v2/search?types=ticket,unknown&q=1"
    Then the response status code should be 200
    And the JSON node "data.grouped_results" should have 1 element
    And the JSON node "data.grouped_results[0].results[0].id" should be equal to 1
    And the JSON node "data.grouped_results[0].results[0].subject" should be equal to "Test"
