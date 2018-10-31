@new
Feature: /ticket_snippet_categories endpoint
  To CRUD DeskPRO ticket snippet categories
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin

  Scenario: I retrieve a list of ticket snippet categories
    Given only the following TextSnippetCategory records exist:
      | #  | Person  | Is Global | Typename |
      | c1 | NULL    | 1         | tickets  |
      | c2 | {admin} | 0         | tickets  |
    And only the following ObjectLang records exist:
      | #  | Ref                          | Ref Type                | Ref Id  | Prop Name | Value             |
      | o1 | text_snippet_categories.~c1~ | text_snippet_categories | ~c1:id~ | title     | Ticket Category 1 |
      | o2 | text_snippet_categories.~c2~ | text_snippet_categories | ~c2:id~ | title     | Ticket Category 2 |

    When I send a GET request to "/api/v2/ticket_snippet_categories"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to "{c1}"
    And the JSON node "data[0].is_global" should be equal to 1
    And the JSON node "data[0].person" should be equal to 0
    And the JSON node "data[0].title" should be equal to "Ticket Category 1"

    And the JSON node "data[1].id" should be equal to "{c2}"
    And the JSON node "data[1].is_global" should be equal to 0
    And the JSON node "data[1].person" should be equal to "{admin}"
    And the JSON node "data[1].title" should be equal to "Ticket Category 2"

  Scenario: I retrieve global categories
    Given only the following TextSnippetCategory records exist:
      | #  | Person  | Is Global | Typename |
      | c1 | NULL    | 1         | tickets  |
      | c2 | {admin} | 0         | tickets  |

    When I send a GET request to "/api/v2/ticket_snippet_categories?global=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{c1}"

  Scenario: I retrieve my categories
    Given agent and user exist
    And only the following TextSnippetCategory records exist:
      | #  | Person  | Is Global | Typename |
      | c1 | NULL    | 1         | tickets  |
      | c2 | {admin} | 0         | tickets  |
      | c3 | {agent} | 0         | tickets  |

    When I send a GET request to "/api/v2/ticket_snippet_categories?my=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{c2}"

  Scenario: I filter by language
    Given only the following Language records exist:
      | #  | Locale | Sys Name |
      | l1 | en-US  | english  |
      | l2 | fr     | french   |
    And only the following TextSnippetCategory records exist:
      | #  | Is Global | Typename |
      | c1 | 1         | tickets  |
      | c2 | 1         | tickets  |
    And only the following ObjectLang records exist:
      | #  | Ref                          | Ref Type                | Ref Id  | Prop Name | Value             | Language |
      | o1 | text_snippet_categories.~c1~ | text_snippet_categories | ~c1:id~ | title     | Ticket Category 1 | {l1}     |
      | o2 | text_snippet_categories.~c2~ | text_snippet_categories | ~c2:id~ | title     | Ticket Category 2 | {l2}     |

    When I send a GET request to "/api/v2/ticket_snippet_categories?language=en-US"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{c1}"

    When I send a GET request to "/api/v2/ticket_snippet_categories?language={l2}"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{c2}"

    When I send a GET request to "/api/v2/ticket_snippet_categories?language=Unknown_Lang"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/ticket_snippet_categories?language=404"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I try to get category from another person
    Given agent and user exist
    And only the following TextSnippetCategory records exist:
      | #  | Person  | Is Global | Typename |
      | c1 | NULL    | 1         | tickets  |
      | c2 | {admin} | 0         | tickets  |
      | c3 | {agent} | 0         | tickets  |

    When I send a GET request to "/api/v2/ticket_snippet_categories/{c3}"
    Then the response status code should be 404

  Scenario: I try to get chat category
    Given agent and user exist
    And only the following TextSnippetCategory records exist:
      | #  | Person | Is Global | Typename |
      | c1 | NULL   | 1         | chat     |

    When I send a GET request to "/api/v2/ticket_snippet_categories/{c1}"
    Then the response status code should be 404

  Scenario: I get list of category snippets
    Given only the following TextSnippetCategory records exist:
      | #  | Person | Is Global | Typename |
      | c1 | NULL   | 1         | tickets  |
    Given only the following TextSnippet records exist:
      | #  | Person  | Category | Shortcut Code   | Is Draft |
      | s1 | NULL    | {c1}     | ticket_snippet1 | 1        |
      | s2 | {admin} | {c1}     | ticket_snippet2 | 0        |

    When I send a GET request to "/api/v2/ticket_snippet_categories/{c1}/snippets"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to "{s1}"
    And the JSON node "data[0].is_draft" should be equal to 1
    And the JSON node "data[0].person" should be equal to 0
    And the JSON node "data[0].category" should be equal to "{c1}"
    And the JSON node "data[0].shortcut_code" should be equal to "ticket_snippet1"

    And the JSON node "data[1].id" should be equal to "{s2}"
    And the JSON node "data[1].is_draft" should be equal to 0
    And the JSON node "data[1].person" should be equal to "{admin}"
    And the JSON node "data[0].category" should be equal to "{c1}"
    And the JSON node "data[1].shortcut_code" should be equal to "ticket_snippet2"

  Scenario: I get list of category snippets from another person
    When I send a GET request to "/api/v2/ticket_snippet_categories/3/snippets"
    Then the response status code should be 404

  Scenario: I try to create a category with empty request
    When I send a POST request to "/api/v2/ticket_snippet_categories"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "too_few_elements"

  Scenario: I try to create a category with wrong object lang format
    When I send a POST request to "/api/v2/ticket_snippet_categories" with body:
    """
{
  "title": [
    {
      "language": 1,
      "value": "My Category"
    },
    "My Category (french)"
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.title.fields.title_1.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I try to create a category with empty object lang data
    When I send a POST request to "/api/v2/ticket_snippet_categories" with body:
    """
{
  "title": [
    {
      "language": 0,
      "value": ""
    }
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.title.fields.title_0.fields.language.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.title.fields.title_0.fields.value.errors[0].code" should be equal to "required"

  Scenario: I create a new ticket snippet category
    Given only the following Language records exist:
      | #  | Locale | Sys Name |
      | l1 | en-US  | english  |
      | l2 | fr     | french   |
      | l3 | ru     | russian  |

    When I send a POST request to "/api/v2/ticket_snippet_categories" with body:
    """
{
  "title": [
    {
      "language": ~l1~,
      "value": "My Category"
    },
    {
      "language": ~l2~,
      "value": "My Category (french)"
    },
    {
      "language": ~l3~,
      "value": "My Category (russian)"
    }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.title" should be equal to "My Category"
    And the JSON node "data.is_global" should be equal to 0

  Scenario: I modify category
    Given only the following Language records exist:
      | #  | Locale | Sys Name |
      | l1 | en-US  | english  |
    And only the following TextSnippetCategory records exist:
      | #  | Person  | Is Global | Typename |
      | c1 | NULL    | 1         | tickets  |

    When I send a PUT request to "/api/v2/ticket_snippet_categories/{c1}" with body:
    """
{
  "title": [
    {
      "language": ~l1~,
      "value": "My Edited Category"
    }
  ],
  "is_global": 1
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_snippet_categories/{c1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{c1}"
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.title" should be equal to "My Edited Category"
    And the JSON node "data.is_global" should be equal to 1

  Scenario: I delete a category
    Given only the following TextSnippetCategory records exist:
      | #  | Person  | Typename |
      | c1 | {admin} | tickets  |

    When I send a DELETE request to "/api/v2/ticket_snippet_categories/{c1}"
    Then the response status code should be 200

  Scenario: I filter by a query string
    Given only the following TextSnippetCategory records exist:
      | #  | Person  | Typename |
      | c1 | {admin} | tickets  |
      | c2 | {admin} | tickets  |
    And only the following ObjectLang records exist:
      | #  | Ref                          | Ref Type                | Ref Id  | Prop Name | Value     |
      | o1 | text_snippet_categories.~c1~ | text_snippet_categories | ~c1:id~ | title     | Category1 |
      | o2 | text_snippet_categories.~c2~ | text_snippet_categories | ~c2:id~ | title     | Category2 |

    When I send a GET request to "/api/v2/ticket_snippet_categories?q=category"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{c1}"
    And the JSON node "data[1].id" should be equal to "{c2}"

    When I send a GET request to "/api/v2/ticket_snippet_categories?q=Category1"
    Then the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to "{c1}"
