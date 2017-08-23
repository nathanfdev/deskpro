@new
Feature: /tickets/{id}/messages endpoint

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And only the following Language records exist:
      | #  | Sys Name |
      | l1 | Lang 1   |
    And only the following TextSnippet records exist:
      | #  | Person  | Shortcut Code   | Is Draft |
      | s1 | NULL    | ticket_snippet1 | 0        |
    And only the following ObjectLang records exist:
      | #  | Ref                | Ref Type      | Ref Id  | Prop Name | Value                       | Language |
      | o1 | text_snippets.~s1~ | text_snippets | ~s1:id~ | snippet   | with appended snippet value | {l1}     |
    And only the following TicketMacro records exist:
      | #  | Person  | Is Global | Title          | Actions                                                                                          |
      | m1 | {admin} | 0         | Ticket macro 1 | [{"type": "add_labels", "options": {"labels": ["label1", "label2"]}}]                            |
      | m2 | {admin} | 0         | Ticket macro 2 | [{"type": "add_labels", "options": {"labels": ["label3", "label4"]}}]                            |
      | m3 | {agent} | 0         | Ticket macro 3 | [{"type": "add_labels", "options": {"labels": ["label5", "label6"]}}]                            |
      | m4 | {admin} | 0         | Ticket macro 4 | [{"type": "reply", "options": {"reply_text": "Prepend text for", "reply_pos": "prepend"}}]       |
      | m5 | {admin} | 0         | Ticket macro 5 | [{"type": "reply", "options": {"reply_text": "My overwrite message", "reply_pos": "overwrite"}}] |
      | m6 | {admin} | 0         | Ticket macro 6 | [{"type": "reply", "options": {"reply_text": "was appended", "reply_pos": "append"}}]            |
      | m7 | {admin} | 0         | Ticket macro 7 | [{"type": "reply_snippet", "options": {"snippet_id": "~s1~", "reply_pos": "append"}}]            |
      | m8 | {admin} | 0         | Ticket macro 8 | [{"type": "status", "options": {"status": "resolved"}}]                                          |
    And only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
    And the "{admin}" record "language" prop is equal to "{l1}"

  Scenario: I reply to a ticket and apply a list of macros
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "macros": [~m1~, ~m2~]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.labels" should have 4 elements
    Then the JSON node "data.labels[0]" should be equal to the string "label1"
    Then the JSON node "data.labels[1]" should be equal to the string "label2"
    Then the JSON node "data.labels[2]" should be equal to the string "label3"
    Then the JSON node "data.labels[3]" should be equal to the string "label4"

  Scenario: I try to apply non-permitted macro
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "macros": [~m2~, ~m3~]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.macros.errors[0].code" should be equal to "bad_choice"

  Scenario: I try to apply a prepend reply macro action
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "macros": [~m4~]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should be equal to the string "Prepend text for\n<br/><br/>\nmy message"

  Scenario: I try to apply an overwrite reply macro action
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "macros": [~m5~]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should be equal to the string "My overwrite message"

  Scenario: I try to apply an append reply macro action
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "macros": [~m6~]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should be equal to the string "my message\n<br/><br/>\nwas appended"

  Scenario: I try to apply an append reply from snippet macro action
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "macros": [~m7~]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should be equal to the string 'my message\n<br/><br/>\nwith appended snippet value'

  Scenario: I send macro reply w/o message
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "macros": [~m5~]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should be equal to the string "My overwrite message"

  Scenario: I send macro reply w/ empty message
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "",
  "macros": [~m5~]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].message" should be equal to the string "My overwrite message"

  Scenario: I try to apply a status macro action
    When I send a POST request to "/api/v2/tickets/{t1}/messages" with body:
    """
{
  "message": "my message",
  "macros": [~m8~]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.status" should be equal to "awaiting_agent"
