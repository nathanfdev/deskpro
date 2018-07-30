@new
Feature: Custom field options

  Background:
    Given I'm authenticated as admin

  Scenario Outline: I retrieve a list of options
    Given only the following <entity> records exist:
      | #    | Type          | Title     | Parent | Options              | Display Order |
      | f1   | single_choice | Field 1   |        |                      |               |
      | c11  |               | Choice 1  | {f1}   |                      | 10            |
      | c12  |               | Choice 2  | {f1}   |                      | 20            |
      | c13  |               | Choice 3  | {f1}   |                      | 30            |
      | c13a |               | Choice 3a | {f1}   | {"parent_id": ~c13~} | 40            |
      | c13b |               | Choice 3b | {f1}   | {"parent_id": ~c13~} | 50            |
      | f2   | single_choice | Field 2   |        |                      |               |
      | c21  |               | Choice 1  | {f2}   |                      | 10            |
      | c22  |               | Choice 2  | {f2}   |                      | 20            |
      | c23  |               | Choice 3  | {f2}   |                      | 30            |
    When I send a GET request to "/api/v2/<action>/{f1}/options?order_by=id&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{c11}"
    And the JSON node "data[1].id" should be equal to "{c12}"
    And the JSON node "data[2].id" should be equal to "{c13}"

    Examples:
      | entity                | action                     |
      | CustomDefTicket       | ticket_custom_fields       |
      | CustomDefPerson       | person_custom_fields       |
      | CustomDefOrganization | organization_custom_fields |
      | CustomDefChat         | user_chat_custom_fields    |

  Scenario Outline: I get an option
    Given only the following <entity> records exist:
      | #   | Type          | Title    | Parent | Options | Display Order |
      | f1  | single_choice | Field 1  |        |         |               |
      | c11 |               | Choice 1 | {f1}   |         | 10            |
      | c12 |               | Choice 2 | {f1}   |         | 20            |
      | c13 |               | Choice 3 | {f1}   |         | 30            |
    When I send a GET request to "/api/v2/<action>/{f1}/options/{c12}"
    Then the JSON node "data.id" should be equal to "{c12}"
    And the JSON node "data.title" should be equal to "Choice 2"
    And the JSON node "data.children" should have 0 elements
    And the JSON node "data.parent" should be equal to "{f1}"
    And the JSON node "data.display_order" should be equal to 20
    And the JSON node "data.translations" should exist

    Examples:
      | entity                | action                     |
      | CustomDefTicket       | ticket_custom_fields       |
      | CustomDefPerson       | person_custom_fields       |
      | CustomDefOrganization | organization_custom_fields |
      | CustomDefChat         | user_chat_custom_fields    |

  Scenario Outline: I try to get unrelated option
    Given only the following <entity> records exist:
      | #   | Type          | Title    | Parent | Options | Display Order |
      | f1  | single_choice | Field 1  |        |         |               |
      | c11 |               | Choice 1 | {f1}   |         | 10            |
      | c12 |               | Choice 2 | {f1}   |         | 20            |
      | f2  | single_choice | Field 2  |        |         |               |
      | c21 |               | Choice 1 | {f2}   |         | 10            |
      | c22 |               | Choice 2 | {f2}   |         | 20            |
    When I send a GET request to "/api/v2/<action>/{f1}/options/{c22}"
    Then the response status code should be 404

    Examples:
      | entity                | action                     |
      | CustomDefTicket       | ticket_custom_fields       |
      | CustomDefPerson       | person_custom_fields       |
      | CustomDefOrganization | organization_custom_fields |
      | CustomDefChat         | user_chat_custom_fields    |
    
  Scenario Outline: I create an option
    Given only the following <entity> records exist:
      | #   | Type          | Title    |
      | f1  | single_choice | Field 1  |
    When I send a POST request to "/api/v2/<action>/{f1}/options" with body:
    """
{
  "title": "My option"
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "My option"

    Examples:
      | entity                | action                     |
      | CustomDefTicket       | ticket_custom_fields       |
      | CustomDefPerson       | person_custom_fields       |
      | CustomDefOrganization | organization_custom_fields |
      | CustomDefChat         | user_chat_custom_fields    |

  Scenario Outline: I update an option
    Given only the following <entity> records exist:
      | #   | Type          | Title    | Parent | Options | Display Order |
      | f1  | single_choice | Field 1  |        |         |               |
      | c11 |               | Choice 1 | {f1}   |         | 10            |
      | c12 |               | Choice 2 | {f1}   |         | 20            |
    When I send a PUT request to "/api/v2/<action>/{f1}/options/{c12}" with body:
    """
{
  "title": "My option"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/<action>/{f1}/options/{c12}"
    Then the JSON node "data.title" should be equal to "My option"

    Examples:
      | entity                | action                     |
      | CustomDefTicket       | ticket_custom_fields       |
      | CustomDefPerson       | person_custom_fields       |
      | CustomDefOrganization | organization_custom_fields |
      | CustomDefChat         | user_chat_custom_fields    |

  Scenario Outline: I create options hierarchy
    Given only the following <entity> records exist:
      | #   | Type          | Title    |
      | f1  | single_choice | Field 1  |
    When I send a POST request to "/api/v2/<action>/{f1}/options" with body:
    """
{
  "title": "My choice",
  "children": [
    {
      "title": "My sub choice 1",
      "children": [
        {
          "title": "My sub sub choice 1"
        },
        {
          "title": "My sub sub choice 2"
        }
      ]
    },
    {
      "title": "My sub choice 2"
    }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.children" should have 2 elements
    And the JSON node "data.children[0].id" should exist
    And the JSON node "data.children[0].title" should be equal to "My sub choice 1"
    And the JSON node "data.children[0].children" should have 2 elements
    And the JSON node "data.children[0].children[0].id" should exist
    And the JSON node "data.children[0].children[0].title" should be equal to "My sub sub choice 1"
    And the JSON node "data.children[0].children[1].id" should exist
    And the JSON node "data.children[0].children[1].title" should be equal to "My sub sub choice 2"
    And the JSON node "data.children[1].id" should exist
    And the JSON node "data.children[1].title" should be equal to "My sub choice 2"

    Examples:
      | entity                | action                     |
      | CustomDefTicket       | ticket_custom_fields       |
      | CustomDefPerson       | person_custom_fields       |
      | CustomDefOrganization | organization_custom_fields |
      | CustomDefChat         | user_chat_custom_fields    |

  Scenario Outline: I add sub choice
    Given only the following <entity> records exist:
      | #    | Type          | Title     | Parent | Options              | Display Order |
      | f1   | single_choice | Field 1   |        |                      |               |
      | c11  |               | Choice 1  | {f1}   |                      | 10            |
      | c12  |               | Choice 2  | {f1}   |                      | 20            |
      | c13  |               | Choice 3  | {f1}   |                      | 30            |
      | c13a |               | Choice 3a | {f1}   | {"parent_id": ~c13~} | 40            |
      | c13b |               | Choice 3b | {f1}   | {"parent_id": ~c13~} | 50            |
    When I send a POST request to "/api/v2/<action>/{c13}/options" with body:
    """
{
  "title": "Choice 3c"
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/<action>/{c13}/options?order_by=id&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].title" should be equal to "Choice 3a"
    And the JSON node "data[1].title" should be equal to "Choice 3b"
    And the JSON node "data[2].title" should be equal to "Choice 3c"

    Examples:
      | entity                | action                     |
      | CustomDefTicket       | ticket_custom_fields       |
      | CustomDefPerson       | person_custom_fields       |
      | CustomDefOrganization | organization_custom_fields |
      | CustomDefChat         | user_chat_custom_fields    |

  Scenario Outline: I delete an option
    Given only the following <entity> records exist:
      | #    | Type          | Title     | Parent | Options              | Display Order |
      | f1   | single_choice | Field 1   |        |                      |               |
      | c11  |               | Choice 1  | {f1}   |                      | 10            |
      | c12  |               | Choice 2  | {f1}   |                      | 20            |
      | c13  |               | Choice 3  | {f1}   |                      | 30            |
      | c13a |               | Choice 3a | {f1}   | {"parent_id": ~c13~} | 40            |
      | c13b |               | Choice 3b | {f1}   | {"parent_id": ~c13~} | 50            |
    When I send a DELETE request to "/api/v2/<action>/{c13}"
    Then the response status code should be 200

    Examples:
      | entity                | action                     |
      | CustomDefTicket       | ticket_custom_fields       |
      | CustomDefPerson       | person_custom_fields       |
      | CustomDefOrganization | organization_custom_fields |
      | CustomDefChat         | user_chat_custom_fields    |
