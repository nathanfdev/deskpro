@new
Feature: Filter data by custom fields
  As an API user

  Background:
    Given I'm authenticated as admin

  Scenario Outline: I filter by custom fields
    Given only the following <custom_def_type> records exist:
      | #                      | Type          | Title               | Parent                |
      | text_field             | text          | Text field          |                       |
      | textarea_field         | textarea      | Textarea field      |                       |
      | toggle_field           | toggle        | Toggle field        |                       |
      | hidden_field           | hidden        | Hidden field        |                       |
      | single_choice_field    | single_choice | Single Choice field |                       |
      | single_choice_v1_field |               | Single Choice v1    | {single_choice_field} |
      | single_choice_v2_field |               | Single Choice v2    | {single_choice_field} |
      | single_choice_v3_field |               | Single Choice v3    | {single_choice_field} |
      | date_field             | date          | Date field          |                       |
      | datetime_field         | datetime      | Datetime field      |                       |

    And I have a <entity_type> record referenced as entity_1
    And I have a <entity_type> record referenced as entity_2
    And I have a <entity_type> record referenced as entity_3
    And I have a <entity_type> record referenced as entity_4
    And I have a <entity_type> record referenced as entity_5
    And I have a <entity_type> record referenced as entity_6
    And I have a <entity_type> record referenced as entity_7
    And I have a <entity_type> record referenced as entity_8
    And I have a <entity_type> record referenced as entity_9
    And I have a <entity_type> record referenced as entity_10
    And the object "entity_1" has "text_field" custom data set to "text"
    And the object "entity_1" has "textarea_field" custom data set to "textarea"
    And the object "entity_2" has "textarea_field" custom data set to "textarea"
    And the object "entity_3" has "toggle_field" custom data set to 1
    And the object "entity_4" has "toggle_field" custom data set to 0
    And the object "entity_5" has "hidden_field" custom data set to "val1"
    And the object "entity_6" has "hidden_field" custom data set to "val2"
    And the object "entity_7" has "single_choice_field" custom data set to "{single_choice_v1_field},{single_choice_v2_field}"
    And the object "entity_8" has "single_choice_field" custom data set to "{single_choice_v2_field}"
    And the object "entity_9" has "date_field" custom data set to "2016-06-11"
    And the object "entity_10" has "date_field" custom data set to "now"
    And the object "entity_10" has "datetime_field" custom data set to "2016-06-06 12:00:00"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~text_field~=text"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_1~"
    And the JSON node "data[0].fields.{text_field}.value" should be equal to the string "text"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~textarea_field~=text"
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].fields.{textarea_field}.value" should be equal to the string "textarea"
    And the JSON node "data[1].fields.{textarea_field}.value" should be equal to the string "textarea"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~textarea_field~=unknown"
    And the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~text_field~=text&<prefix>_field.~textarea_field~=text"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_1~"
    And the JSON node "data[0].fields.{text_field}.value" should be equal to the string "text"
    And the JSON node "data[0].fields.{textarea_field}.value" should be equal to the string "textarea"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~toggle_field~=1"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_3~"
    And the JSON node "data[0].fields.{toggle_field}.value" should be equal to 1

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~toggle_field~=0"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_4~"
    And the JSON node "data[0].fields.{toggle_field}.value" should be equal to 0

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~hidden_field~=val1"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_5~"
    And the JSON node "data[0].fields.{hidden_field}.value" should be equal to the string "val1"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~hidden_field~=val2"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_6~"
    And the JSON node "data[0].fields.{hidden_field}.value" should be equal to the string "val2"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~hidden_field~=unknown"
    And the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~single_choice_field~[]=~single_choice_v1_field~"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_7~"
    And the JSON node "data[0].fields.{single_choice_field}.value[0]" should be equal to "{single_choice_v1_field}"
    And the JSON node "data[0].fields.{single_choice_field}.value[1]" should be equal to "{single_choice_v2_field}"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~single_choice_field~[]=~single_choice_v1_field~&<prefix>_field.~single_choice_field~[]=~single_choice_v2_field~&order_by=id&order_dir=asc"
    And the response status code should be 200
    And the JSON node "data" should have 2 element
    And the JSON node "data[0].id" should be equal to "~entity_7~"
    And the JSON node "data[1].id" should be equal to "~entity_8~"
    And the JSON node "data[1].fields.{single_choice_field}.value[0]" should be equal to "{single_choice_v2_field}"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~single_choice_field~[]=~single_choice_v3_field~"
    And the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~date_field~[from]=2016-06-10&order_by=id&order_dir=asc"
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "~entity_9~"
    And the JSON node "data[1].id" should be equal to "~entity_10~"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~date_field~[to]=2016-06-12"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_9~"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~date_field~[from]=2016-06-10&<prefix>_field.~date_field~[to]=2016-06-12"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_9~"

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~date_field~[to]=2016-06-10"
    And the response status code should be 200
    And the JSON node "data" should have 0 elements

    When I send a GET request to "/api/v2/<endpoint>?<prefix>_field.~date_field~=today"
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "~entity_10~"

    Examples:
      | custom_def_type       | entity_type  | endpoint      | prefix   |
      | CustomDefPerson       | User         | people        | person   |
      | CustomDefOrganization | Organization | organizations | org      |
      | CustomDefTicket       | Ticket       | tickets       | ticket   |
      | CustomDefChat         | Chat         | user_chats    | chat     |
      | CustomDefFeedback     | Feedback     | feedback      | feedback |
