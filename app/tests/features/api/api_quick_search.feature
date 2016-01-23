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
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    Given I set permission "agent_tickets.view_unassigned" = 1 for "registered" usergroup
    Given the setting "elastica.enabled" is set to 0
    When I send a GET request to "/api/v2/search?q=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "grouped_results[0].type" should be equal to "article"
    And the JSON node "grouped_results[0].results" should have 1 elements
    And the JSON node "grouped_results[0].results[0].id" should be equal to 1
    And the JSON node "grouped_results[0].results[0].title" should be equal to "A test article"
    And the JSON node "grouped_results[1].type" should be equal to "download"
    And the JSON node "grouped_results[1].results" should have 1 elements
    And the JSON node "grouped_results[1].results[0].id" should be equal to 1
    And the JSON node "grouped_results[1].results[0].title" should be equal to "Test Download #1"
    And the JSON node "grouped_results[2].type" should be equal to "feedback"
    And the JSON node "grouped_results[2].results" should have 1 elements
    And the JSON node "grouped_results[2].results[0].id" should be equal to 1
    And the JSON node "grouped_results[2].results[0].title" should be equal to "Test feedback 1"
    And the JSON node "grouped_results[3].type" should be equal to "news"
    And the JSON node "grouped_results[3].results" should have 1 elements
    And the JSON node "grouped_results[3].results[0].id" should be equal to 1
    And the JSON node "grouped_results[3].results[0].title" should be equal to "Test News #1"
    And the JSON node "grouped_results[4].type" should be equal to "ticket"
    And the JSON node "grouped_results[4].results" should have 1 elements
    And the JSON node "grouped_results[4].results[0].id" should be equal to 1
    And the JSON node "grouped_results[4].results[0].subject" should be equal to "Test"
    And the JSON node "grouped_results[5].type" should be equal to "person"
    And the JSON node "grouped_results[5].results" should have 1 elements
    And the JSON node "grouped_results[5].results[0].id" should be equal to 1
    And the JSON node "grouped_results[5].results[0].name" should be equal to "Link Admin"
    And the JSON node "grouped_results[6].type" should be equal to "organization"
    And the JSON node "grouped_results[6].results" should have 1 elements
    And the JSON node "grouped_results[6].results[0].id" should be equal to 1
    And the JSON node "grouped_results[6].results[0].name" should be equal to "Organization 1"

  Scenario Outline: I search ticket by id with view restriction
    Given I set permission "agent_people.use" = <ticket_use> for "registered" usergroup
    Given I set permission "agent_tickets.use" = <view_unassigned> for "registered" usergroup
    When I send a GET request to "/api/v2/search?q=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "grouped_results[4].type" should be equal to "ticket"
    And the JSON node "grouped_results[4].results" should have 0 elements

    Examples:
      | ticket_use | view_unassigned |
      | 0          | 0               |
      | 1          | 0               |

  Scenario: I search by full email
    Given I set permission "agent_people.use" = 1 for "registered" usergroup
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    Given I set permission "agent_tickets.view_unassigned" = 1 for "registered" usergroup
    Given the setting "elastica.enabled" is set to 0
    When I send a GET request to "/api/v2/search?q=admin@deskpro.dev"
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
    And the JSON node "grouped_results[5].results" should have 1 elements
    And the JSON node "grouped_results[5].results[0].id" should be equal to 1
    And the JSON node "grouped_results[5].results[0].name" should be equal to "Link Admin"
    And the JSON node "grouped_results[6].type" should be equal to "organization"
    And the JSON node "grouped_results[6].results" should have 0 elements
    And the JSON node "grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "grouped_results[7].results" should have 0 elements

    When I send a GET request to "/api/v2/search?q=user@deskpro.dev"
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
    And the JSON node "grouped_results[5].results" should have 1 elements
    And the JSON node "grouped_results[5].results[0].id" should be equal to 3
    And the JSON node "grouped_results[5].results[0].name" should be equal to "Ganon User"
    And the JSON node "grouped_results[6].type" should be equal to "organization"
    And the JSON node "grouped_results[6].results" should have 0 elements
    And the JSON node "grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "grouped_results[7].results" should have 0 elements

  Scenario: I search by email domain
    Given I set permission "agent_people.use" = 1 for "registered" usergroup
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    Given I set permission "agent_tickets.view_unassigned" = 1 for "registered" usergroup
    Given the setting "elastica.enabled" is set to 0
    When I send a GET request to "/api/v2/search?q=@deskpro"
    Then the response should be in JSON
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
    And the JSON node "grouped_results[5].results" should have 4 elements
    And the JSON node "grouped_results[5].results[3].emails[0]" should be equal to "admin@deskpro.dev"
    And the JSON node "grouped_results[5].results[2].emails[0]" should be equal to "agent@deskpro.dev"
    And the JSON node "grouped_results[5].results[1].emails[0]" should be equal to "user@deskpro.dev"
    And the JSON node "grouped_results[5].results[0].emails[0]" should be equal to "deleted-agent@deskpro.dev"
    And the JSON node "grouped_results[6].type" should be equal to "organization"
    And the JSON node "grouped_results[6].results" should have 0 elements
    And the JSON node "grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "grouped_results[7].results" should have 0 elements
