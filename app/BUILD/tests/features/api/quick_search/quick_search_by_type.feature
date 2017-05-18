@new
Feature: Quick Search
  I want to check search by entity type

  Background:
    Given I'm authenticated as "admin"
    And the setting "elastica.enabled" is set to 0
    And only the following "Feedback" records exist:
      | #        | person  | is_reviewed | slug      | title          | content   | status |
      | feedback | {admin} | 1           | feedback1 | Test Feedback1 | Feedback1 | active |
    And only the following "Article" records exist:
      | #       | slug     | title         | content  | status  |
      | article | article1 | Test Article1 | Article1 | visible |
    And only the following "Download" records exist:
      | #        | slug      | title          | status    |
      | download | Download1 | Test Download1 | published |
    And only the following "News" records exist:
      | #    | slug  | title      | status    |
      | news | News1 | Test News1 | published |
    And only the following "Organization" records exist:
      | #            | name       | summary                                    |
      | organization | Admin Vector ltd | Admin Vector is a common fake org name in Russia |
    And only the following "Ticket" records exist:
      | #      | status        | ref  | subject     |
      | ticket | awaiting_user | AAAA | Test Ticket1 |
    And only the following "Chat" records exist:
      | #    | subject    |
      | chat | Test Chat1 |

  Scenario: I search by specific types
    When I send a GET request to "/api/v2/search?q=Test&types=article,ticket,news"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results" should have 3 elements
    And the JSON node "data.grouped_results[0].results[0].id" should be equal to "{article}"
    And the JSON node "data.grouped_results[1].results[0].id" should be equal to "{ticket}"
    And the JSON node "data.grouped_results[2].results[0].id" should be equal to "{news}"

  Scenario: I search by people and orgs
    When I send a GET request to "/api/v2/search/people_and_orgs?q=Admin"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results" should have 2 elements
    And the JSON node "data.grouped_results[0].results[0].name" should be equal to "Admin Admin"
    And the JSON node "data.grouped_results[1].results[0].id" should be equal to "{organization}"

  Scenario Outline: I search by entity type
    When I send a GET request to "/api/v2/search/<type>?q=<query>"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].id" should be equal to "<query>"
    And the JSON node "data[0].<param>" should be equal to "<value>"

    Examples:
      | type              | param         | value             | query          |
      | article           | title         | Test Article1     | {article}      |
      | download          | title         | Test Download1    | {download}     |
      | feedback          | title         | Test Feedback1    | {feedback}     |
      | news              | title         | Test News1        | {news}         |
      | ticket            | subject       | Test Ticket1      | {ticket}       |
      | person            | primary_email | admin@deskpro.dev | {admin}        |
      | organization      | name          | Admin Vector ltd  | {organization} |
      | chat_conversation | subject       | Test Chat1        | {chat}         |

  Scenario: I try to search by unsupported type
    When I send a GET request to "/api/v2/search/unknown?q=1"
    Then the response status code should be 404

    When I send a GET request to "/api/v2/search?types=ticket,unknown&q={ticket}"
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Unknown types: unknown"
