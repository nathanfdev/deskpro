@new
Feature: /tasks/{id}/linked_items/(articles|chats|tickets) endpoints
  To CRUD DeskPRO task linked items
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And "test@deskpro.dev" user exists
    And only the following "Task" records exist:
      | #  | creator            | title     | visibility |
      | t1 | {test@deskpro.dev} | Test task | 1          |

  Scenario Outline: I try to create item with malformed request
    When I send a POST request to "/api/v2/tasks/{t1}/linked_items/<type>"
    Then the response status code should be 400
    And the JSON node "errors.fields.item.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.item.errors[0].message" should contain "This value should not be blank."

    Examples:
      | type     |
      | articles |
      | chats    |
      | tickets  |

  Scenario Outline: I check validation
    When I send a POST request to "/api/v2/tasks/{t1}/linked_items/<type>" with body:
    """
{
  "item": 1,
  "unknown": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: unknown"

    Examples:
    | type     |
    | articles |
    | chats    |
    | tickets  |

  Scenario Outline: I create linked items
    Given only the following "Article" records exist:
      | #        | slug     | title    | content  | status  |
      | article1 | article1 | Article1 | Article1 | visible |
      | article2 | article2 | Article2 | Article2 | visible |
    And only the following "Ticket" records exist:
      | #       | status        | ref  | subject |
      | ticket1 | awaiting_user | AAAA | Ticket1 |
      | ticket2 | awaiting_user | BBBB | Ticket2 |
    And only the following "Chat" records exist:
      | #     | subject |
      | chat1 | Chat1   |
      | chat2 | Chat2   |
    When I send a POST request to "/api/v2/tasks/{t1}/linked_items/<type>" with body:
    """
{
  "item": <ref1>
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "<ref1>"
    And the JSON node "data.<param>" should be equal to "<value1>"

    When I send a POST request to "/api/v2/tasks/{t1}/linked_items/<type>" with body:
    """
{
  "item": <ref2>
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "<ref2>"
    And the JSON node "data.<param>" should be equal to "<value2>"

    Examples:
      | type     | param   | value1   | value2   | ref1       | ref2       |
      | articles | title   | Article1 | Article2 | ~article1~ | ~article2~ |
      | chats    | subject | Chat1    | Chat2    | ~chat1~    | ~chat2~    |
      | tickets  | subject | Ticket1  | Ticket2  | ~ticket1~  | ~ticket2~  |

  Scenario Outline: I try to create a linked item with the same id
    Given only the following "Article" records exist:
      | #        | slug     | title    | content  | status  |
      | article | article | Article | Article | visible |
    And only the following "Ticket" records exist:
      | #      | status        | ref  | subject |
      | ticket | awaiting_user | AAAA | Ticket  |
    And only the following "Chat" records exist:
      | #    | subject |
      | chat | Chat    |
    And only the following "TaskLinkedArticle" records exist:
      | #   | article   | task |
      | tla | {article} | {t1} |
    And only the following "TaskLinkedTicket" records exist:
      | #   | ticket   | task |
      | tlt | {ticket} | {t1} |
    And only the following "TaskLinkedChat" records exist:
      | #   | chat   | task |
      | tlc | {chat} | {t1} |
    When I send a POST request to "/api/v2/tasks/{t1}/linked_items/<type>" with body:
    """
{
  "item": <ref>
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.item.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.item.errors[0].message" should contain "This value already exists in the system."

    Examples:
      | type     | ref       |
      | articles | ~article~ |
      | chats    | ~chat~    |
      | tickets  | ~ticket~  |

  Scenario Outline: I retrieve a list of linked items
    Given only the following "Article" records exist:
      | #        | slug     | title    | content  | status  |
      | article | article | Article | Article | visible |
    And only the following "Ticket" records exist:
      | #      | status        | ref  | subject |
      | ticket | awaiting_user | AAAA | Ticket  |
    And only the following "Chat" records exist:
      | #    | subject |
      | chat | Chat    |
    And only the following "TaskLinkedArticle" records exist:
      | #   | article   | task |
      | tla | {article} | {t1} |
    And only the following "TaskLinkedTicket" records exist:
      | #   | ticket   | task |
      | tlt | {ticket} | {t1} |
    And only the following "TaskLinkedChat" records exist:
      | #   | chat   | task |
      | tlc | {chat} | {t1} |

    When I send a GET request to "/api/v2/tasks/{t1}/linked_items/<type>"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements

    And the JSON node "data[0].id" should be equal to "<ref>"
    And the JSON node "data[0].<param>" should be equal to "<value>"

    Examples:
      | type     | param   | value   | ref       |
      | articles | title   | Article | ~article~ |
      | chats    | subject | Chat    | ~chat~    |
      | tickets  | subject | Ticket  | ~ticket~  |

  Scenario Outline: I get single linked item
    Given only the following "Article" records exist:
      | #       | slug    | title   | content | status  | person |
      | article | article | Article | Article | visible | {me}   |
    And only the following "Ticket" records exist:
      | #      | status        | ref  | subject | person |
      | ticket | awaiting_user | AAAA | Ticket  | {me}   |
    And only the following "Chat" records exist:
      | #    | subject | person |
      | chat | Chat    | {me}   |
    And only the following "TaskLinkedArticle" records exist:
      | #   | article   | task |
      | tla | {article} | {t1} |
    And only the following "TaskLinkedTicket" records exist:
      | #   | ticket   | task |
      | tlt | {ticket} | {t1} |
    And only the following "TaskLinkedChat" records exist:
      | #   | chat   | task |
      | tlc | {chat} | {t1} |
    When I send a GET request to "/api/v2/tasks/{t1}/linked_items/<type>/<ref>?include=person"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "<ref>"
    And the JSON node "data.<param>" should be equal to "<value>"
    And the JSON node "linked.person.{me}.primary_email" should be equal to "admin@deskpro.dev"

    Examples:
      | type     | param   | value   | ref       |
      | articles | title   | Article | ~article~ |
      | chats    | subject | Chat    | ~chat~    |
      | tickets  | subject | Ticket  | ~ticket~  |

  Scenario Outline: I delete a linked item
    Given only the following "Article" records exist:
      | #        | slug     | title    | content  | status  |
      | article | article | Article | Article | visible |
    And only the following "Ticket" records exist:
      | #      | status        | ref  | subject |
      | ticket | awaiting_user | AAAA | Ticket  |
    And only the following "Chat" records exist:
      | #    | subject |
      | chat | Chat    |
    And only the following "TaskLinkedArticle" records exist:
      | #   | article   | task |
      | tla | {article} | {t1} |
    And only the following "TaskLinkedTicket" records exist:
      | #   | ticket   | task |
      | tlt | {ticket} | {t1} |
    And only the following "TaskLinkedChat" records exist:
      | #   | chat   | task |
      | tlc | {chat} | {t1} |
    When I send a DELETE request to "/api/v2/tasks/{t1}/linked_items/<type>/<ref>"
    Then the response status code should be 200

    Examples:
      | type     | ref       |
      | articles | ~article~ |
      | chats    | ~chat~    |
      | tickets  | ~ticket~  |
