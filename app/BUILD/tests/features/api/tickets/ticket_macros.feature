@new
Feature: /ticket_macros endpoint
  To CRUD DeskPRO tickets
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And I reset ticket logs
    And I have only default brand
    And only the following Department records exist:
      | #  | Parent | Brands           | Title        | Is Tickets Enabled |
      | d1 | NULL   | [{defaultBrand}] | Department 1 | 1                  |
      | d2 | NULL   | [{defaultBrand}] | Department 2 | 1                  |
      | d3 | NULL   | [{defaultBrand}] | Department 3 | 1                  |
    And only the following Ticket records exist:
      | #  | Subject  | Agent   | Department |
      | t1 | Ticket 1 | {admin} | {d1}       |
    And the setting "core_tickets.field_validation_ticket_prod_agent_required" is set to 1
    And the setting "core_tickets.field_validation_ticket_pri_agent_required" is set to 1
    And the setting "core_tickets.field_validation_ticket_cat_agent_required" is set to 1
    And the setting "core_tickets.field_validation_ticket_work_agent_required" is set to 1
    And the only default ticket layout exists with fields:
      | user_layout | agent_layout |

  Scenario: I retrieve a list of macros with sideloading
    Given agent and user exist
    And only the following TicketMacro records exist:
      | #  | Person  | Is Global | Title              | Actions                                                                                                                             |
      | m1 | {admin} | 0         | Ticket macro 1     | [{"type": "agent", "options": {"agent": -1}}]                                                                                       |
      | m2 | {admin} | 0         | Ticket macro 2     | [{"type": "add_labels", "options": {"labels": ["label1", "label2", "label3"]}}, {"type": "language", "options": {"language": "1"}}] |
      | m3 | NULL    | 1         | Ticket macro 3     | [{"type": "reply", "options": {"reply_text": "My reply text.", "reply_pos": "append"}}]                                             |
      | m3 | {agent} | 0         | Another user macro | [{"type": "reply", "options": {}]                                                                                                   |

    When I send a GET request to "/api/v2/ticket_macros?include=person"
    And the response status code should be 200
    And the JSON node "data" should have 3 elements

    And the JSON node "data[0].person" should be equal to "{admin}"
    And the JSON node "data[0].title" should be equal to "Ticket macro 1"
    And the JSON node "data[0].is_enabled" should be equal to 1
    And the JSON node "data[0].is_global" should be equal to 0
    And the JSON node "data[0].actions" should have 1 element
    And the JSON node "data[0].actions[0].type" should be equal to "agent"
    And the JSON node "data[0].actions[0].options.agent" should be equal to "-1"

    And the JSON node "data[1].person" should be equal to "{admin}"
    And the JSON node "data[1].title" should be equal to "Ticket macro 2"
    And the JSON node "data[1].is_enabled" should be equal to 1
    And the JSON node "data[1].is_global" should be equal to 0
    And the JSON node "data[1].actions" should have 2 elements
    And the JSON node "data[1].actions[0].type" should be equal to "add_labels"
    And the JSON node "data[1].actions[0].options.labels" should have 3 elements
    And the JSON node "data[1].actions[0].options.labels[0]" should be equal to "label1"
    And the JSON node "data[1].actions[0].options.labels[1]" should be equal to "label2"
    And the JSON node "data[1].actions[0].options.labels[2]" should be equal to "label3"
    And the JSON node "data[1].actions[1].type" should be equal to "language"
    And the JSON node "data[1].actions[1].options.language" should be equal to 1

    And the JSON node "data[2].person" should be equal to 0
    And the JSON node "data[2].title" should be equal to "Ticket macro 3"
    And the JSON node "data[2].is_enabled" should be equal to 1
    And the JSON node "data[2].is_global" should be equal to 1
    And the JSON node "data[2].actions" should have 1 element
    And the JSON node "data[2].actions[0].type" should be equal to "reply"
    And the JSON node "data[2].actions[0].options.reply_text" should be equal to "My reply text."
    And the JSON node "data[2].actions[0].options.reply_pos" should be equal to "append"

    And the JSON node "linked.person.{admin}.primary_email" should be equal to "admin@deskpro.dev"

  Scenario: I get a macro with sideloading
    Given only the following TicketMacro records exist:
      | #  | Person  | Title          | Actions                                                     |
      | m1 | {admin} | Ticket macro 1 | [{"type": "department", "options": {"department": "~d2~"}}] |

    When I send a GET request to "/api/v2/ticket_macros/{m1}?include=person"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Ticket macro 1"
    And the JSON node "linked.person.{admin}.primary_email" should be equal to "admin@deskpro.dev"

  Scenario: I get a macro w/o sideloading
    Given only the following TicketMacro records exist:
      | #  | Person  | Title          | Actions                                                     |
      | m1 | {admin} | Ticket macro 1 | [{"type": "department", "options": {"department": "~d2~"}}] |

    When I send a GET request to "/api/v2/ticket_macros/{m1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to "Ticket macro 1"
    And the JSON node "linked" should have 0 elements

  Scenario: I try to get not existing macro
    When I send a GET request to "/api/v2/ticket_macros/0"
    Then the response status code should be 404

  Scenario: I try to get a macro from another user
    Given agent and user exist
    And only the following TicketMacro records exist:
      | #  | Person  | Title          | Actions                                                     |
      | m1 | {admin} | Ticket macro 1 | [{"type": "department", "options": {"department": "~d2~"}}] |
      | m2 | {agent} | Ticket macro 2 | [{"type": "department", "options": {"department": "~d2~"}}] |

    When I send a GET request to "/api/v2/ticket_macros/{m2}"
    Then the response status code should be 404

  Scenario Outline: I try to apply failed macro (built-in fields validation)
    Given the setting "core.use_<setting_name>" is set to 1
    And the ticket layout exists for "d2" department with fields:
      | agent_layout |
      | <field_name> |
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And only the following TicketMacro records exist:
      | #  | Person  | Title        | Actions                                                     |
      | m1 | {admin} | Ticket macro | [{"type": "department", "options": {"department": "~d2~"}}] |

    When I send a POST request to "/api/v2/ticket_macros/{m1}/apply/{t1}"
    Then the response status code should be 400
    And the JSON node "errors.fields.<field_name>.errors[0].code" should be equal to "required"

    Examples:
      | entity_type    | field_name | setting_name    |
      | Product        | product    | product         |
      | TicketCategory | category   | ticket_category |
      | TicketWorkflow | workflow   | ticket_workflow |
      | TicketPriority | priority   | ticket_priority |

    Scenario: I try to apply failed macro (custom data validation)
      Given only the following custom ticket fields exist:
        | #          | Type | Title      | Options                  |
        | text_field | text | Text field | {"agent_required": true} |
      And the ticket layout exists for "d2" department with fields:
        | agent_layout              |
        | ticket_field_{text_field} |
      And only the following TicketMacro records exist:
        | #  | Person  | Title        | Actions                                                     |
        | m1 | {admin} | Ticket macro | [{"type": "department", "options": {"department": "~d2~"}}] |

      When I send a POST request to "/api/v2/ticket_macros/{m1}/apply/{t1}"
      Then the response status code should be 400
      And the JSON node "errors.fields.custom_data.fields.custom_data_{text_field}.errors[0].code" should be equal to "required"

  Scenario: I apply labels and language macro
    Given only the following Language records exist:
      | #  | Title  |
      | l1 | Lang 1 |
    And only the following TicketMacro records exist:
      | #  | Person  | Title        | Actions                                                                                                                                |
      | m1 | {admin} | Ticket macro | [{"type": "add_labels", "options": {"labels": ["label1", "label2", "label3"]}}, {"type": "language", "options": {"language": "~l1~"}}] |

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.language" should be equal to 0
    And the JSON node "data.labels" should have 0 elements

    When I send a POST request to "/api/v2/ticket_macros/{m1}/apply/{t1}"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.language" should be equal to "{l1}"
    And the JSON node "data.labels" should have 3 elements
    And the JSON node "data.labels[0]" should be equal to the string "label1"
    And the JSON node "data.labels[1]" should be equal to the string "label2"
    And the JSON node "data.labels[2]" should be equal to the string "label3"
    And the "{t1}" ticket should have the following logs:
      | type             |
      | changed_labels   |
      | changed_language |

  Scenario: I apply a macro with reply action
    Given only the following TicketMacro records exist:
      | #  | Person  | Title              | Actions                                                                                 |
      | m1 | {admin} | Reply ticket macro | [{"type": "reply", "options": {"reply_text": "My reply text.", "reply_pos": "append"}}] |

    When I send a POST request to "/api/v2/ticket_macros/{m1}/apply/{t1}"
    Then the response status code should be 204

    When I send a POST request to "/api/v2/ticket_macros/{m1}/apply/{t1}"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].message" should be equal to "My reply text."
    And the JSON node "data[1].message" should be equal to "My reply text."
    And the "{t1}" ticket should have "message_created" log
