@new
Feature: JSON API fetch ids only

  Background:
   Given I'm authenticated as "admin"

  Scenario Outline: I search by ID
    And only the following "Feedback" records exist:
      | #        | person  | is_reviewed | slug      | title     | content   | status |
      | feedback | {admin} | 1           | feedback1 | Feedback1 | Feedback1 | active |
    And only the following "Article" records exist:
      | #       | slug     | title    | content  | status  |
      | article | article1 | Article1 | Article1 | visible |
    And only the following "Download" records exist:
      | #        | slug      | title     |  status   |
      | download | Download1 | Download1 | published |
    And only the following "News" records exist:
      | #    | slug  | title | status    |
      | news | News1 | News1 | published |
    And only the following "Organization" records exist:
      | #            | name       | summary                                    |
      | organization | Vector ltd | Vector is a common fake org name in Russia |
    And only the following "Ticket" records exist:
      | #      | status        | ref  | subject |
      | ticket | awaiting_user | AAAA | Ticket  |
    When I send a GET request to "/api/v2/search?q=<ref>&ids_only=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results[<order>].type" should be equal to "<type>"
    And the JSON node "data.grouped_results[<order>].results" should have 1 element
    And the JSON node "data.grouped_results[<order>].results[0]" should be equal to "<ref>"

    Examples:
      | ref            | type         | order |
      | {article}      | article      | 0     |
      | {download}     | download     | 1     |
      | {feedback}     | feedback     | 2     |
      | {news}         | news         | 3     |
      | {ticket}       | ticket       | 4     |
      | {me}           | person       | 5     |
      | {organization} | organization | 6     |

  Scenario Outline: I get lists of data
    Given I re-fill ticket search table
    And only the following "Ticket" records exist:
      | #       | status        | ref  | subject |
      | ticket1 | awaiting_user | AAAA | Ticket1 |
      | ticket2 | awaiting_user | BBBB | Ticket2 |
    When I send a GET request to "/api/v2/<endpoint>?order_by=id&order_dir=asc&ids_only=1"
    Then the JSON node "data" should have <count> element
    And the JSON node "data[0]" should be equal to <id1>
    And the JSON node "data[1]" should be equal to <id2>

    Examples:
      | endpoint | count | id1         | id2         |
# is the people endpoint something we can ensure it has ONLY records?
#      | people   | 2     | "{admin}"   | "{agent}"   |
      | tickets  | 2     | "{ticket1}" | "{ticket2}" |
#      | ticket_filters/1/tickets | 2     | 2   | 4   |
