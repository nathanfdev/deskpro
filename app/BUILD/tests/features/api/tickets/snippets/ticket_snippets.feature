@new
Feature: /ticket_snippets endpoint
  To CRUD DeskPRO ticket snippets
  As an API user
  I want an API endpoint

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And agent and user exist
    And only the following TextSnippetCategory records exist:
      | #  | Person  | Typename |
      | c1 | {admin} | tickets  |
      | c2 | {admin} | tickets  |
    And only the following Language records exist:
      | #  | Locale | Lang Code |
      | l1 | en_US  | eng       |
      | l2 | fr    | fre        |
      | l3 | ru    | rus        |

  Scenario: I retrieve a list of text snippets
    Given only the following TextSnippet records exist:
      | #  | Person  | Category | Shortcut Code   | Is Draft |
      | s1 | NULL    | {c1}     | ticket_snippet1 | 0        |
      | s2 | {admin} | {c1}     | ticket_snippet2 | 1        |
      | s3 | {admin} | {c2}     | ticket_snippet3 | 1        |
      | s4 | {admin} | {c2}     | ticket_snippet4 | 1        |
      | s5 | {agent} | {c1}     | ticket_snippet5 | 1        |
    And only the following ObjectLang records exist:
      | #  | Ref                | Ref Type      | Ref Id  | Prop Name | Value            |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | title     | Ticket Snippet 1 |
      | o2 | text_snippets.~s2~ | text_snippets | ~s2:id~ | title     | Ticket Snippet 2 |
      | o3 | text_snippets.~s3~ | text_snippets | ~s3:id~ | title     | Ticket Snippet 3 |
      | o4 | text_snippets.~s4~ | text_snippets | ~s4:id~ | title     | Ticket Snippet 4 |

    When I send a GET request to "/api/v2/ticket_snippets"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].id" should be equal to "{s1}"
    And the JSON node "data[0].title" should be equal to "Ticket Snippet 1"
    And the JSON node "data[0].category" should be equal to "{c1}"
    And the JSON node "data[0].person" should be equal to 0
    And the JSON node "data[0].shortcut_code" should be equal to "ticket_snippet1"
    And the JSON node "data[0].is_draft" should be equal to 0

    And the JSON node "data[1].id" should be equal to "{s2}"
    And the JSON node "data[1].title" should be equal to "Ticket Snippet 2"
    And the JSON node "data[1].category" should be equal to "{c1}"
    And the JSON node "data[1].person" should be equal to "{admin}"
    And the JSON node "data[1].shortcut_code" should be equal to "ticket_snippet2"
    And the JSON node "data[1].is_draft" should be equal to 1

    And the JSON node "data[2].id" should be equal to "{s3}"
    And the JSON node "data[2].title" should be equal to "Ticket Snippet 3"
    And the JSON node "data[2].category" should be equal to "{c2}"
    And the JSON node "data[2].person" should be equal to "{admin}"
    And the JSON node "data[2].shortcut_code" should be equal to "ticket_snippet3"
    And the JSON node "data[2].is_draft" should be equal to 1

    And the JSON node "data[3].id" should be equal to "{s4}"
    And the JSON node "data[3].title" should be equal to "Ticket Snippet 4"
    And the JSON node "data[3].category" should be equal to "{c2}"
    And the JSON node "data[3].person" should be equal to "{admin}"
    And the JSON node "data[3].shortcut_code" should be equal to "ticket_snippet4"
    And the JSON node "data[3].is_draft" should be equal to 1

  Scenario: I get single snippet
    Given only the following TextSnippet records exist:
      | #  | Person  | Category | Shortcut Code   | Is Draft |
      | s1 | NULL    | {c1}     | ticket_snippet1 | 0        |

    When I send a GET request to "/api/v2/ticket_snippets/{s1}"
    Then the response status code should be 200

  Scenario: I try to get non existing snippet
    When I send a GET request to "/api/v2/ticket_snippets/0"
    Then the response status code should be 404

  Scenario: I try to get chat snippet
    Given the following TextSnippetCategory records exist:
      | #  | Person  | Typename |
      | c3 | {admin} | chat     |
    And only the following TextSnippet records exist:
      | #  | Person  | Category | Shortcut Code   | Is Draft |
      | s1 | NULL    | {c3}     | ticket_snippet1 | 0        |

    When I send a GET request to "/api/v2/ticket_snippets/{s1}"
    Then the response status code should be 404

  Scenario: I retrieve a list of text snippets filtered by category
    Given only the following TextSnippet records exist:
      | #  | Person  | Category | Shortcut Code   | Is Draft |
      | s1 | NULL    | {c1}     | ticket_snippet1 | 0        |
      | s2 | NULL    | {c2}     | ticket_snippet2 | 0        |
      | s3 | NULL    | {c1}     | ticket_snippet3 | 0        |

    When I send a GET request to "/api/v2/ticket_snippets?category={c1}"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{s1}"
    And the JSON node "data[1].id" should be equal to "{s3}"

  Scenario: I filter by person
    Given only the following TextSnippet records exist:
      | #  | Person  | Category | Shortcut Code   | Is Draft |
      | s1 | {admin} | {c1}     | ticket_snippet1 | 0        |
      | s2 | NULL    | {c2}     | ticket_snippet2 | 0        |
      | s3 | {agent} | {c1}     | ticket_snippet3 | 0        |

    When I send a GET request to "/api/v2/ticket_snippets?global=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{s2}"

    When I send a GET request to "/api/v2/ticket_snippets?my=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{s1}"

  Scenario: I filter by draft
    Given only the following TextSnippet records exist:
      | #  | Person  | Category | Shortcut Code   | Is Draft |
      | s1 | {admin} | {c1}     | ticket_snippet1 | 0        |
      | s2 | {admin} | {c1}     | ticket_snippet1 | 1        |

    When I send a GET request to "/api/v2/ticket_snippets?draft=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{s2}"

    When I send a GET request to "/api/v2/ticket_snippets?draft=0"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{s1}"

  Scenario: I filter by language

    And only the following TextSnippet records exist:
      | #  | Category |
      | s1 | {c1}     |
      | s2 | {c1}     |
    And only the following ObjectLang records exist:
      | #  | Ref                | Ref Type      | Ref Id  | Prop Name | Value            | Language |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | title     | Ticket Snippet 1 | {l1}     |
      | o2 | text_snippets.~s2~ | text_snippets | ~s2:id~ | title     | Ticket Snippet 2 | {l2}     |

    When I send a GET request to "/api/v2/ticket_snippets?language=en_US"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{s1}"

    When I send a GET request to "/api/v2/ticket_snippets?language=fre"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{s2}"

    When I send a GET request to "/api/v2/ticket_snippets?language={l1}"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{s1}"

    When I send a GET request to "/api/v2/ticket_snippets?language=Unknown_Lang"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/ticket_snippets?language={l3}"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I retrieve ticket snippet content
    Given only the following TextSnippet records exist:
      | #  | Category |
      | s1 | {c1}     |
    And only the following ObjectLang records exist:
      | #  | Ref                | Ref Type      | Ref Id  | Prop Name | Value                | Language |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | title     | Snippet title (en)   | {l1}     |
      | o2 | text_snippets.~s1~ | text_snippets | ~s1:id~ | snippet   | Snippet content (en) | {l1}     |
      | o3 | text_snippets.~s1~ | text_snippets | ~s1:id~ | title     | Snippet title (fr)   | {l2}     |
      | o4 | text_snippets.~s1~ | text_snippets | ~s1:id~ | snippet   | Snippet content (fr) | {l2}     |

    When I send a GET request to "/api/v2/ticket_snippets/{s1}/content"
    Then the response status code should be 200
    And the JSON node "data.en_US.title" should be equal to the string "Snippet title (en)"
    And the JSON node "data.en_US.content" should be equal to the string "Snippet content (en)"
    And the JSON node "data.fr.title" should be equal to the string "Snippet title (fr)"
    And the JSON node "data.fr.content" should be equal to the string "Snippet content (fr)"

  Scenario: I retrieve ticket snippet content with replacements
    Given only the following TextSnippet records exist:
      | #  | Category |
      | s1 | {c1}     |
    And only the following ObjectLang records exist:
      | #  | Ref                | Ref Type      | Ref Id  | Prop Name | Value                               | Language |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | title     | Snippet for {{ ticket.subject }}    | {l1}     |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | snippet   | Created by {{ ticket.person.name }} | {l1}     |
    And only the following Ticket records exist:
      | #  | Subject  | Person |
      | t1 | Ticket 1 | {user} |

    When I send a GET request to "/api/v2/ticket_snippets/{s1}/content?ticket={t1}"
    Then the response status code should be 200
    And the JSON node "data.en_US.title" should be equal to "Snippet for Ticket 1"
    And the JSON node "data.en_US.content" should be equal to "Created by User User"

  Scenario: I retrieve ticket snippets with sideloading
    Given only the following TextSnippet records exist:
      | #  | Category |
      | s1 | {c1}     |
    And only the following ObjectLang records exist:
      | #  | Ref                | Ref Type      | Ref Id  | Prop Name | Value                               | Language |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | title     | Snippet for {{ ticket.subject }}    | {l1}     |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | snippet   | Created by {{ ticket.person.name }} | {l1}     |
    And only the following Ticket records exist:
      | #  | Subject  | Person |
      | t1 | Ticket 1 | {user} |

    When I send a GET request to "/api/v2/ticket_snippets?include=text_snippet_content"
    Then the response status code should be 200
    And the JSON node "linked.text_snippet_content.{s1}.en_US.title" should be equal to the string "Snippet for {{ ticket.subject }}"
    And the JSON node "linked.text_snippet_content.{s1}.en_US.content" should be equal to the string "Created by {{ ticket.person.name }}"

    When I send a GET request to "/api/v2/ticket_snippets?include=text_snippet_content&ticket={t1}"
    Then the response status code should be 200
    And the JSON node "linked.text_snippet_content.{s1}.en_US.title" should be equal to the string "Snippet for Ticket 1"
    And the JSON node "linked.text_snippet_content.{s1}.en_US.content" should be equal to the string "Created by User User"

  Scenario: I sideload snippet w/o content
    Given only the following TextSnippet records exist:
      | #  | Category |
      | s1 | {c1}     |

    When I send a GET request to "/api/v2/ticket_snippets/{s1}/content"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/ticket_snippets/{s1}?include=text_snippet_content"
    Then the response status code should be 200
    And the JSON node "linked.text_snippet_content.{s1}" should have 0 elements

  Scenario: I try to create a ticket snippet with empty request
    When I send a POST request to "/api/v2/ticket_snippets"
    Then the response status code should be 400

    And the JSON node "errors.fields.title.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.snippet.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.category.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.shortcut_code.errors[0].code" should be equal to "required"

  Scenario: I try to create a ticket snippet with wrong object lang data
    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{
  "title": [
    {
      "language": 0,
      "value": ""
    }
  ],
  "snippet": [
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

    And the JSON node "errors.fields.snippet.fields.snippet_0.fields.language.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.snippet.fields.snippet_0.fields.value.errors[0].code" should be equal to "required"

  Scenario: I try to select chat category
    Given the following TextSnippetCategory records exist:
      | #  | Person  | Typename |
      | c3 | {admin} | chat     |

    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{
  "category": ~c3~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.category.errors[0].code" should be equal to "bad_choice"

  Scenario: I try to select category from another person
    Given the following TextSnippetCategory records exist:
      | #  | Person  | Typename |
      | c3 | {agent} | tickets  |

    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{
  "category": ~c3~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.category.errors[0].code" should be equal to "bad_choice"

  Scenario: I add a new text snippet
    When I send a POST request to "/api/v2/ticket_snippets" with body:
    """
{
  "category": ~c1~,
  "shortcut_code": "my_snippet",
  "title": [
    {
      "language": ~l1~,
      "value": "My Snippet"
    }
  ],
  "snippet": [
    {
      "language": ~l1~,
      "value": "My Snippet Content"
    }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.title" should be equal to "My Snippet"
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.shortcut_code" should be equal to "my_snippet"
    And the JSON node "data.is_draft" should be equal to 0

  Scenario: I modify text snippet
    Given only the following TextSnippet records exist:
      | #  | Person  | Category | Is Draft |
      | s1 | {admin} | {c1}     | 0        |

    When I send a PUT request to "/api/v2/ticket_snippets/{s1}" with body:
    """
{
  "category": ~c2~,
  "is_draft": 1
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/ticket_snippets/{s1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{s1}"
    And the JSON node "data.category" should be equal to "{c2}"
    And the JSON node "data.is_draft" should be equal to 1

  Scenario: I delete ticket snippet
    Given only the following TextSnippet records exist:
      | #  | Person  | Category |
      | s1 | {admin} | {c1}     |

    When I send a DELETE request to "/api/v2/ticket_snippets/{s1}"
    Then the response status code should be 200

  Scenario: I filter by a query string
    Given only the following TextSnippet records exist:
      | #  | Person  | Category | Shortcut Code   |
      | s1 | {admin} | {c1}     | ticket_snippet1 |
      | s2 | {admin} | {c1}     | ticket_snippet2 |
    And only the following ObjectLang records exist:
      | #  | Ref                | Ref Type      | Ref Id  | Prop Name | Value         |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | title     | SnippetTitle1 |
      | o2 | text_snippets.~s1~ | text_snippets | ~s1:id~ | snippet   | SnippetValue1 |
      | o3 | text_snippets.~s2~ | text_snippets | ~s2:id~ | title     | SnippetTitle2 |
      | o4 | text_snippets.~s2~ | text_snippets | ~s2:id~ | snippet   | SnippetValue2 |

    When I send a GET request to "/api/v2/ticket_snippets?q=snippet"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{s1}"
    And the JSON node "data[1].id" should be equal to "{s2}"

    When I send a GET request to "/api/v2/ticket_snippets?q=snippettitle1"
    Then the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to "{s1}"

    When I send a GET request to "/api/v2/ticket_snippets?q=ticket_snippet2"
    Then the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to "{s2}"
