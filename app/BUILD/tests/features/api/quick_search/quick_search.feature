@new
Feature: Quick Search
  Doctrine search adapter

  Background:
    Given I'm authenticated as "admin"
    And the setting "elastica.enabled" is set to 0
    And I set permission "articles.use" = 1 for "registered" usergroup
    And I set permission "downloads.use" = 1 for "registered" usergroup
    And I set permission "news.use" = 1 for "registered" usergroup
    And I set permission "feedback.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.use" = 1 for "registered" usergroup
    And I set permission "agent_tickets.view_unassigned" = 1 for "registered" usergroup

  Scenario Outline: I search by ID
    Given I set permission "agent_people.use" = 1 for "registered" usergroup
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
    When I send a GET request to "/api/v2/search?q=<ref>"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results[<order>].type" should be equal to "<type>"
    And the JSON node "data.grouped_results[<order>].results" should have 1 element
    And the JSON node "data.grouped_results[<order>].results[0].id" should be equal to "<ref>"

    Examples:
      | ref            | type         | order |
      | {article}      | article      | 0     |
      | {download}     | download     | 1     |
      | {feedback}     | feedback     | 2     |
      | {news}         | news         | 3     |
      | {ticket}       | ticket       | 4     |
      | {me}           | person       | 5     |
      | {organization} | organization | 6     |

  Scenario: I search person by full email
    Given "user@deskpro.dev" user exists
    And only the following "Organization" records exist:
      | #            | name       | summary                                    |
      | organization | Vector ltd | Vector is a common fake org name in Russia |
    And the "user" is in "organization" organization
    And the "admin" is in "organization" organization
    When I send a GET request to "/api/v2/search?q=admin@deskpro.dev"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results[0].id" should be equal to "{admin}"
    And the JSON node "data.grouped_results[5].results[0].name" should be equal to "Admin Admin"
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 1 elements
    And the JSON node "data.grouped_results[6].results[0].id" should be equal to "{organization}"
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

    When I send a GET request to "/api/v2/search?q=user@deskpro.dev"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 1 element
    And the JSON node "data.grouped_results[5].results[0].id" should be equal to "{user}"
    And the JSON node "data.grouped_results[5].results[0].name" should be equal to "User User"
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 1 elements
    And the JSON node "data.grouped_results[6].results[0].id" should be equal to "{organization}"
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search people by partial email (domain)
    Given agent and user exist
    And only the following "Organization" records exist:
      | #             | name       | summary                                    |
      | organization1 | Vector ltd | Vector is a common fake org name in Russia |
      | organization2 | List  ltd  | List is a double linked structure          |
    And the "admin" is in "organization1" organization
    And the "agent" is in "organization1" organization
    And the "user" is in "organization2" organization
    When I send a GET request to "/api/v2/search?q=@deskpro"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 3 elements
    And the JSON node "data.grouped_results[5].results[0].emails[0]" should be equal to "admin@deskpro.dev"
    And the JSON node "data.grouped_results[5].results[1].emails[0]" should be equal to "user@deskpro.dev"
    And the JSON node "data.grouped_results[5].results[2].emails[0]" should be equal to "agent@deskpro.dev"
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 2 elements
    And the JSON node "data.grouped_results[6].results[0].id" should be equal to "{organization1}"
    And the JSON node "data.grouped_results[6].results[1].id" should be equal to "{organization2}"
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

    When I send a GET request to "/api/v2/search?q=@dev"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[5].results" should have 0 elements

  Scenario: I search people by partial email (name)
    Given agent and user exist
    And only the following "Organization" records exist:
      | #             | name       | summary                                    |
      | organization1 | Vector ltd | Vector is a common fake org name in Russia |
      | organization2 | List  ltd  | List is a double linked structure          |
    And the "admin" is in "organization1" organization
    And the "agent" is in "organization1" organization
    And the "user" is in "organization2" organization
    When I send a GET request to "/api/v2/search?q=agent@"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 1 element
    And the JSON node "data.grouped_results[5].results[0].emails[0]" should be equal to "agent@deskpro.dev"
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 1 element
    And the JSON node "data.grouped_results[6].results[0].id" should be equal to "{organization1}"
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search articles by title
    Given only the following "Article" records exist:
      | #        | slug     | title    | content  | status  |
      | article1 | article1 | Article1 | Article1 | visible |
      | article2 | article2 | Article2 | Article2 | visible |
    When I send a GET request to "/api/v2/search?q=Article"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 2 elements
    And the JSON node "data.grouped_results[0].results[0].id" should be equal to "{article1}"
    And the JSON node "data.grouped_results[0].results[1].id" should be equal to "{article2}"
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search downloads by title
    Given only the following "Download" records exist:
      | #         | slug      | title     |  status   |
      | download1 | Download1 | Download1 | published |
      | download2 | Download2 | Download2 | published |
    When I send a GET request to "/api/v2/search?q=Download"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 2 elements
    And the JSON node "data.grouped_results[1].results[0].id" should be equal to "{download1}"
    And the JSON node "data.grouped_results[1].results[1].id" should be equal to "{download2}"
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search feedback by title
    Given only the following "Feedback" records exist:
      | #         | person  | is_reviewed | slug      | title     | content   | status |
      | feedback1 | {admin} | 1           | feedback1 | Feedback1 | Feedback1 | active |
      | feedback2 | {admin} | 1           | feedback2 | Feedback2 | Feedback2 | active |
    When I send a GET request to "/api/v2/search?q=Feedback"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 2 elements
    And the JSON node "data.grouped_results[2].results[0].id" should be equal to "{feedback1}"
    And the JSON node "data.grouped_results[2].results[1].id" should be equal to "{feedback2}"
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search news by title
    Given only the following "News" records exist:
      | #     | slug  | title | status    |
      | news1 | News1 | News1 | published |
      | news2 | News2 | News2 | published |
    When I send a GET request to "/api/v2/search?q=News"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 2 elements
    And the JSON node "data.grouped_results[3].results[0].id" should be equal to "{news1}"
    And the JSON node "data.grouped_results[3].results[1].id" should be equal to "{news2}"
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search ticket by subject
    And only the following "Ticket" records exist:
      | #       | status        | ref  | subject  |
      | ticket1 | awaiting_user | AAAA | Ticket1  |
      | ticket2 | awaiting_user | BBBB | Ticket2  |
    When I send a GET request to "/api/v2/search?q=Ticket"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 2 elements
    And the JSON node "data.grouped_results[4].results[0].subject" should be equal to "Ticket1"
    And the JSON node "data.grouped_results[4].results[1].subject" should be equal to "Ticket2"
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search organization by name
    Given only the following "Organization" records exist:
      | #            | name       | summary                                    |
      | vector | Vector ltd | Vector is a common fake org name in Russia |
    And the "me" is in "vector" organization
    When I send a GET request to "/api/v2/search?q=Vector"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 1 elements
    And the JSON node "data.grouped_results[5].results[0].id" should be equal to "{me}"
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 1 elements
    And the JSON node "data.grouped_results[6].results[0].name" should be equal to "Vector ltd"
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search by word
    Given only the following "Feedback" records exist:
      | #        | person  | is_reviewed | slug      | title     | content   | status |
      | feedback | {admin} | 1           | feedback1 | Test Feedback1 | Feedback1 | active |
    And only the following "Article" records exist:
      | #       | slug     | title    | content  | status  |
      | article | article1 | Test Article1 | Article1 | visible |
    And only the following "Download" records exist:
      | #        | slug      | title     |  status   |
      | download | Download1 | Test Download1 | published |
    And only the following "News" records exist:
      | #    | slug  | title | status    |
      | news | News1 | Test News1 | published |
    And only the following "Organization" records exist:
      | #            | name       | summary                                    |
      | organization | Test Vector ltd | Vector is a common fake org name in Russia |
    And only the following "Ticket" records exist:
      | #      | status        | ref  | subject |
      | ticket | awaiting_user | AAAA | Test Ticket  |
    When I send a GET request to "/api/v2/search?q=Test"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 1 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 1 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 1 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 1 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 1 element
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 1 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search ticket by ref
    Given only the following "Ticket" records exist:
      | #      | status        | ref         | subject     |
      | ticket | awaiting_user | AA-BB-CC-DD | Test Ticket |
    When I send a GET request to "/api/v2/search?q=AA-BB-CC-DD"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 1 element
    And the JSON node "data.grouped_results[4].results[0].id" should be equal to "{ticket}"
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I search by label
    Given only the following "LabelDef" records exist:
      | label_type | label  | color | total |
      | ticket     | label1 | red   | 0     |
      | ticket     | label2 | blue  | 0     |
      | feedback   | label2 | blue  | 0     |
    Given only the following "Ticket" records exist:
      | #       | status        | ref         | subject       |
      | ticket1 | awaiting_user | AA-BB-CC-DD | Test Ticket 1 |
      | ticket2 | awaiting_user | AA-BB-CC-DE | Test Ticket 2 |
      | ticket3 | awaiting_user | AA-BB-CC-DF | Test Ticket 3 |
    And only the following "LabelTicket" records exist:
      | ticket    | label  |
      | {ticket1} | label1 |
      | {ticket2} | label1 |
      | {ticket3} | label2 |
    And only the following "Feedback" records exist:
      | #        | person  | is_reviewed | slug      | title     | content   | status |
      | feedback | {admin} | 1           | feedback1 | Feedback1 | Feedback1 | active |
    And only the following "LabelFeedback" records exist:
      | feedback   | label  |
      | {feedback} | label2 |
    When I send a GET request to "/api/v2/search?q=[label1]"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 2 elements
    And the JSON node "data.grouped_results[4].results[0].id" should be equal to "{ticket1}"
    And the JSON node "data.grouped_results[4].results[1].id" should be equal to "{ticket2}"
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

    When I send a GET request to "/api/v2/search?q=label1"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 2 elements
    And the JSON node "data.grouped_results[4].results[0].id" should be equal to "{ticket1}"
    And the JSON node "data.grouped_results[4].results[1].id" should be equal to "{ticket2}"
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

    When I send a GET request to "/api/v2/search?q=label2"
    Then the response should be in JSON
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 1 element
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 1 element
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario: I send empty query request without "agent_people.use" permission
    Given I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"
    And I set permission "agent_people.use" = 0 for "registered" usergroup
    When I send a GET request to "/api/v2/search"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[5].type" should be equal to "chat_conversation"

  Scenario: I send empty query request with "agent_people.use" permission
    Given I set permission "agent_people.use" = 1 for "registered" usergroup
    Given the setting "elastica.enabled" is set to 0
    When I send a GET request to "/api/v2/search"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results[0].type" should be equal to "article"
    And the JSON node "data.grouped_results[0].results" should have 0 elements
    And the JSON node "data.grouped_results[1].type" should be equal to "download"
    And the JSON node "data.grouped_results[1].results" should have 0 elements
    And the JSON node "data.grouped_results[2].type" should be equal to "feedback"
    And the JSON node "data.grouped_results[2].results" should have 0 elements
    And the JSON node "data.grouped_results[3].type" should be equal to "news"
    And the JSON node "data.grouped_results[3].results" should have 0 elements
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements
    And the JSON node "data.grouped_results[5].type" should be equal to "person"
    And the JSON node "data.grouped_results[5].results" should have 0 elements
    And the JSON node "data.grouped_results[6].type" should be equal to "organization"
    And the JSON node "data.grouped_results[6].results" should have 0 elements
    And the JSON node "data.grouped_results[7].type" should be equal to "chat_conversation"
    And the JSON node "data.grouped_results[7].results" should have 0 elements

  Scenario Outline: I search ticket by id with view restriction
    Given I set permission "agent_people.use" = <ticket_use> for "registered" usergroup
    And I set permission "agent_tickets.use" = <view_unassigned> for "registered" usergroup
    And my request is authenticated to "agent"
    When I send a GET request to "/api/v2/search?q=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.grouped_results[4].type" should be equal to "ticket"
    And the JSON node "data.grouped_results[4].results" should have 0 elements

    Examples:
      | ticket_use | view_unassigned |
      | 0          | 0               |
      | 1          | 0               |
