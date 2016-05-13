@tickets
Feature: /ticket_macros endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And the setting "core.use_product" is set to 1
    And the setting "core.use_ticket_priority" is set to 1
    And the setting "core.use_ticket_category" is set to 1
    And the setting "core.use_ticket_workflow" is set to 1
    And the setting "core_tickets.field_validation_ticket_prod_agent_required" is set to 0
    And the setting "core_tickets.field_validation_ticket_pri_agent_required" is set to 0
    And the setting "core_tickets.field_validation_ticket_cat_agent_required" is set to 0
    And the setting "core_tickets.field_validation_ticket_work_agent_required" is set to 0

  @reinstall
  Scenario: I retrieve a list of macros with sideloading
    When I send a GET request to "/api/v2/ticket_macros?include=person"
    And the response status code should be 200
    And the JSON node "data" should have 7 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].person" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Update ticket macro 1"
    And the JSON node "data[0].is_enabled" should be equal to 1
    And the JSON node "data[0].is_global" should be equal to 1
    And the JSON node "data[0].actions" should have 2 elements
    And the JSON node "data[0].actions[0].type" should be equal to "agent"
    And the JSON node "data[0].actions[0].options.agent" should be equal to "-1"
    And the JSON node "data[0].actions[1].type" should be equal to "department"
    And the JSON node "data[0].actions[1].options.department" should be equal to 1

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].person" should be equal to 1
    And the JSON node "data[1].title" should be equal to "Update ticket macro 2"
    And the JSON node "data[1].is_enabled" should be equal to 1
    And the JSON node "data[1].is_global" should be equal to 0
    And the JSON node "data[1].actions" should have 2 elements
    And the JSON node "data[1].actions[0].type" should be equal to "add_labels"
    And the JSON node "data[1].actions[0].options.labels" should have 3 elements
    And the JSON node "data[1].actions[0].options.labels[0]" should be equal to "label1"
    And the JSON node "data[1].actions[0].options.labels[1]" should be equal to "label2"
    And the JSON node "data[1].actions[0].options.labels[2]" should be equal to "label3"
    And the JSON node "data[1].actions[1].type" should be equal to "language"
    And the JSON node "data[1].actions[1].options.language" should be equal to 2

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].person" should be equal to 1
    And the JSON node "data[2].title" should be equal to "Update ticket macro 3"
    And the JSON node "data[2].is_enabled" should be equal to 1
    And the JSON node "data[2].is_global" should be equal to 0
    And the JSON node "data[2].actions" should have 3 elements
    And the JSON node "data[2].actions[0].type" should be equal to "add_labels"
    And the JSON node "data[2].actions[0].options.labels" should have 3 elements
    And the JSON node "data[2].actions[0].options.labels[0]" should be equal to "label4"
    And the JSON node "data[2].actions[0].options.labels[1]" should be equal to "label5"
    And the JSON node "data[2].actions[0].options.labels[2]" should be equal to "label6"
    And the JSON node "data[2].actions[1].type" should be equal to "language"
    And the JSON node "data[2].actions[1].options.language" should be equal to 2
    And the JSON node "data[2].actions[2].type" should be equal to "department"
    And the JSON node "data[2].actions[2].options.department" should be equal to 1

    And the JSON node "data[3].id" should be equal to 5
    And the JSON node "data[3].person" should be equal to 2
    And the JSON node "data[3].title" should be equal to "Update ticket macro 5"
    And the JSON node "data[3].is_enabled" should be equal to 1
    And the JSON node "data[3].is_global" should be equal to 1
    And the JSON node "data[3].actions[0].type" should be equal to "status"
    And the JSON node "data[3].actions[0].options.status" should be equal to "awaiting_agent"

    And the JSON node "data[4].id" should be equal to 6
    And the JSON node "data[4].person" should be equal to 1
    And the JSON node "data[4].title" should be equal to "Update and reply ticket macro 1"
    And the JSON node "data[4].is_enabled" should be equal to 1
    And the JSON node "data[4].is_global" should be equal to 0
    And the JSON node "data[4].actions[0].type" should be equal to "reply"
    And the JSON node "data[4].actions[0].options.reply_text" should be equal to "My reply text."
    And the JSON node "data[4].actions[0].options.reply_pos" should be equal to "append"
    And the JSON node "data[4].actions[1].type" should be equal to "department"
    And the JSON node "data[4].actions[1].options.department" should be equal to 1

    And the JSON node "data[5].id" should be equal to 7
    And the JSON node "data[5].person" should be equal to 1
    And the JSON node "data[5].title" should be equal to "Fail validation macro 1"
    And the JSON node "data[5].actions[0].type" should be equal to "reply"
    And the JSON node "data[5].actions[0].options.reply_text" should be equal to 0
    And the JSON node "data[5].actions[1].type" should be equal to "department"
    And the JSON node "data[5].actions[1].options.department" should be equal to 1
    And the JSON node "data[5].actions[2].type" should be equal to "add_cc"
    And the JSON node "data[5].actions[2].options.add_emails" should be equal to "user@deskpro.dev"
    And the JSON node "data[5].actions[3].type" should be equal to "agent"
    And the JSON node "data[5].actions[3].options.agent" should be equal to "3"
    And the JSON node "data[5].actions[4].type" should be equal to "ticket_field[8]"
    And the JSON node "data[5].actions[4].options.custom_fields.field_8[0]" should be equal to 1
    And the JSON node "data[5].actions[4].options.custom_fields.field_8[1]" should be equal to 9

    And the JSON node "data[6].id" should be equal to 8
    And the JSON node "data[6].person" should be equal to 1
    And the JSON node "data[6].title" should be equal to "Fail validation macro 2"
    And the JSON node "data[6].actions[0].type" should be equal to "ticket_field[6]"
    And the JSON node "data[6].actions[0].options.custom_fields.field_6" should be equal to "abc"
    And the JSON node "data[6].actions[1].type" should be equal to "department"
    And the JSON node "data[6].actions[1].options.department" should be equal to 2

    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.person.1.primary_email" should be equal to "admin@deskpro.dev"

  Scenario: I get a macro with sideloading
    When I send a GET request to "/api/v2/ticket_macros/1?include=person,organization"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "Update ticket macro 1"
    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.person.1.primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "linked.organization.1.id" should be equal to 1
    And the JSON node "linked.organization.1.name" should be equal to "Organization 1"


  Scenario: I get a macro w/o sideloading
    When I send a GET request to "/api/v2/ticket_macros/1"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "Update ticket macro 1"
    And the JSON node "linked" should have 0 elements

  Scenario: I try to get not existing macro
    When I send a GET request to "/api/v2/ticket_macros/404"
    Then the response status code should be 404

  Scenario: I try to get a macro from another user
    When I send a GET request to "/api/v2/ticket_macros/4"
    Then the response status code should be 404

  Scenario: I try to apply failed macro (layout custom data validation)
    Given the setting "core_tickets.field_validation_ticket_prod_agent_required" is set to 1
    And the setting "core_tickets.field_validation_ticket_pri_agent_required" is set to 1
    And the setting "core_tickets.field_validation_ticket_cat_agent_required" is set to 1
    And the setting "core_tickets.field_validation_ticket_work_agent_required" is set to 1
    When I send a POST request to "/api/v2/ticket_macros/8/apply/1"
    Then the response status code should be 400

    And the JSON node "errors.fields.product.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.product.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.priority.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.priority.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.category.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.category.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.workflow.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.workflow.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.custom_data.fields.custom_data_6.errors[0].code" should be equal to "length_too_short"
    And the JSON node "errors.fields.custom_data.fields.custom_data_6.errors[0].message" should be equal to "This value is too short. It should have 10 characters or more."
    And the JSON node "errors.fields.person.fields.custom_data.fields.custom_data_6.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.person.fields.custom_data.fields.custom_data_6.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.organization.fields.custom_data.fields.custom_data_6.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.organization.fields.custom_data.fields.custom_data_6.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I apply a macro
    Given I reset ticket with id=1 logs
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the JSON node "data.language" should be equal to 0
    And the JSON node "data.labels" should have 0 elements

    When I send a POST request to "/api/v2/ticket_macros/2/apply/1"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the JSON node "data.language" should be equal to 2
    And the JSON node "data.labels" should have 3 elements
    And the JSON node "data.labels[0]" should be equal to the string "label1"
    And the JSON node "data.labels[1]" should be equal to the string "label2"
    And the JSON node "data.labels[2]" should be equal to the string "label3"
    And ticket with id=1 has logs:
      | type               |
      | changed_labels     |
      | changed_language   |
      | changed_department |

  Scenario: I apply a macro with reply action
    Given I reset ticket with id=1 logs
    When I send a POST request to "/api/v2/ticket_macros/6/apply/1"
    Then the response status code should be 204

    When I send a POST request to "/api/v2/ticket_macros/6/apply/1"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the JSON node "data.department" should be equal to 1

    When I send a GET request to "/api/v2/tickets/1/messages"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].message" should be equal to "My reply text."
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].message" should be equal to "My reply text."
    And ticket with id=1 has "message_created" log
