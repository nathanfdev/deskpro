@new
Feature: /snippets endpoint
  To CRUD DeskPRO ticket snippets
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as agent
    And the setting "beta_features.new_snippets" is set to 1
    And agent and user exist
    And only the following Language records exist:
      | #  | Locale | Sys Name |
      | l1 | en-US  | english  |
      | l2 | fr     | french   |
      | l3 | ru     | russian  |

  Scenario: I create a snippet form myself
    When I send a POST request to "/api/v2/snippets?inline_sideloads=true&include=snippet_translation" with body:
    """
{
  "title": "Test Snippet",
  "translations": [
    {
      "language": ~l1~,
      "content": "Test Snippet content"
    }
  ],
  "is_ownership_global": false,
  "ownership_teams": [],
  "labels": ["Label1"],
  "types": ["ticket"]
}
    """
    Then the response status code should be 201
    And the header "Location" should be equal to "/api/v2/snippets/{lastCreatedId}"
    And the JSON node "data.title" should be equal to "Test Snippet"
    And the JSON node "data.translations[0].language" should be equal to "{l1}"
    And the JSON node "data.translations[0].content" should be equal to "Test Snippet content"
    And the JSON node "data.is_ownership_global" should be false
    And the JSON node "data.ownership_teams" should have 0 elements
    And the JSON node "data.person" should be equal to "{agent}"
    And the JSON node "data.labels[0]" should be equal to "Label1"
    And the JSON node "data.types[0]" should be equal to "ticket"

  Scenario: I retrieve a list of snippets
    Given I'm authenticated as admin
    And only the following Snippet records exist:
      | #  | Person  | Title         | Shortcut Code   | Is Draft | Types              | Is Ownership global |
      | s1 | NULL    | Title         | ticket_snippet1 | 0        | ["ticket"]         | 1                   |
      | s2 | {admin} | Admin Snippet | ticket_snippet2 | 0        | ["ticket"]         | 1                   |
      | s3 | {admin} | Admin Draft   | ticket_snippet3 | 1        | ["ticket"]         | 0                   |
      | s4 | {admin} | Title         | ticket_snippet4 | 1        | ["ticket", "chat"] | 0                   |
      | s5 | {agent} | Agent Snippet | ticket_snippet5 | 0        | ["chat"]           | 1                   |

    And only the following SnippetTranslation records exist:
      | #   | Language | Snippet | Content            |
      | st1 | ~l1~     | ~s1~    | Example content    |
      | st2 | ~l2~     | ~s1~    | Contenu d'exemple  |
      | st3 | ~l1~     | ~s2~    | Example content    |
      | st4 | ~l3~     | ~s2~    | Пример содержимого |
      | st5 | ~l1~     | ~s3~    | Example content    |
      | st6 | ~l1~     | ~s4~    | Example content    |
      | st7 | ~l1~     | ~s5~    | Example content    |

    When I send a GET request to "/api/v2/snippets?inline_sideloads=true&include=snippet_translation"
    Then the response status code should be 200
    And the JSON node "data" should have 5 elements

    And the JSON node "data[0].id" should be equal to "{s1}"
    And the JSON node "data[0].title" should be equal to "Title"
    And the JSON node "data[0].person" should be equal to 0
    And the JSON node "data[0].types[0]" should be equal to "ticket"
    And the JSON node "data[0].shortcut_code" should be equal to "ticket_snippet1"
    And the JSON node "data[0].is_draft" should be equal to 0
    And the JSON node "data[0].is_ownership_global" should be equal to 1
    And the JSON node "data[0].translations[0].language" should be equal to "{l1}"
    And the JSON node "data[0].translations[0].content" should be equal to "Example content"
    And the JSON node "data[0].translations[1].language" should be equal to "{l2}"
    And the JSON node "data[0].translations[1].content" should be equal to "Contenu d'exemple"

    And the JSON node "data[1].id" should be equal to "{s2}"
    And the JSON node "data[1].title" should be equal to "Admin Snippet"
    And the JSON node "data[1].person" should be equal to "{admin}"
    And the JSON node "data[1].types[0]" should be equal to "ticket"
    And the JSON node "data[1].shortcut_code" should be equal to "ticket_snippet2"
    And the JSON node "data[1].is_draft" should be equal to 0
    And the JSON node "data[1].is_ownership_global" should be equal to 1
    And the JSON node "data[1].translations[0].language" should be equal to "{l1}"
    And the JSON node "data[1].translations[0].content" should be equal to "Example content"
    And the JSON node "data[1].translations[1].language" should be equal to "{l3}"
    And the JSON node "data[1].translations[1].content" should be equal to "Пример содержимого"

    And the JSON node "data[2].id" should be equal to "{s3}"
    And the JSON node "data[2].title" should be equal to "Admin Draft"
    And the JSON node "data[2].person" should be equal to "{admin}"
    And the JSON node "data[2].types[0]" should be equal to "ticket"
    And the JSON node "data[2].shortcut_code" should be equal to "ticket_snippet3"
    And the JSON node "data[2].is_draft" should be equal to 1
    And the JSON node "data[2].is_ownership_global" should be equal to 0
    And the JSON node "data[2].translations[0].language" should be equal to "{l1}"
    And the JSON node "data[2].translations[0].content" should be equal to "Example content"

    And the JSON node "data[3].id" should be equal to "{s4}"
    And the JSON node "data[3].title" should be equal to "Title"
    And the JSON node "data[3].person" should be equal to "{admin}"
    And the JSON node "data[3].types[0]" should be equal to "ticket"
    And the JSON node "data[3].types[1]" should be equal to "chat"
    And the JSON node "data[3].shortcut_code" should be equal to "ticket_snippet4"
    And the JSON node "data[3].is_draft" should be equal to 1
    And the JSON node "data[3].is_ownership_global" should be equal to 0
    And the JSON node "data[3].translations[0].language" should be equal to "{l1}"
    And the JSON node "data[3].translations[0].content" should be equal to "Example content"

    And the JSON node "data[4].id" should be equal to "{s5}"
    And the JSON node "data[4].title" should be equal to "Agent Snippet"
    And the JSON node "data[4].person" should be equal to "{agent}"
    And the JSON node "data[4].types[0]" should be equal to "chat"
    And the JSON node "data[4].shortcut_code" should be equal to "ticket_snippet5"
    And the JSON node "data[4].is_draft" should be equal to 0
    And the JSON node "data[4].is_ownership_global" should be equal to 1
    And the JSON node "data[4].translations[0].language" should be equal to "{l1}"
    And the JSON node "data[4].translations[0].content" should be equal to "Example content"
