@new
Feature: Ticket custom fields
  To customize DeskPRO to fit my needs
  As a DeskPRO user
  I want to be able to create custom ticket fields

  Background:
    Given I'm authenticated as admin
    And only the following custom ticket fields exist:
      | #                       | Type           | Title               | Parent                 |
      | text_field              | text           | Text field          |                        |
      | textarea_field          | textarea       | Textarea field      |                        |
      | date_field              | date           | Date field          |                        |
      | datetime_field          | datetime       | Datetime field      |                        |
      | single_choice_field     | single_choice  | Single Choice field |                        |
      | single_choice_v1_field  |                | Single Choice v1    | {single_choice_field}  |
      | single_choice_v2_field  |                | Single Choice v2    | {single_choice_field}  |
      | single_choice_v3_field  |                | Single Choice v3    | {single_choice_field}  |
      | checkbox_group_field    | checkbox_group | Multi Choice field  |                        |
      | checkbox_group_v1_field |                | Multi Choice v1     | {checkbox_group_field} |
      | checkbox_group_v2_field |                | Multi Choice v2     | {checkbox_group_field} |
      | checkbox_group_v3_field |                | Multi Choice v3     | {checkbox_group_field} |

  Scenario: I retrieve a list of custom fields
    When I send a GET request to "/api/v2/ticket_custom_fields"
    And the response status code should be 200
    And the JSON node "data" should have 6 elements
    And the JSON node "data[0].title" should be equal to "Text field"
    And the JSON node "data[1].title" should be equal to "Textarea field"
    And the JSON node "data[2].title" should be equal to "Date field"
    And the JSON node "data[3].title" should be equal to "Datetime field"
    And the JSON node "data[4].title" should be equal to "Single Choice field"
    And the JSON node "data[4].choices" should have 3 elements
    And the JSON node "data[5].title" should be equal to "Multi Choice field"
    And the JSON node "data[5].choices" should have 3 elements

  Scenario: I get a custom field
    When I send a GET request to "/api/v2/ticket_custom_fields/{text_field}"
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Text field"
    And the JSON node "data.parent" should be null
    And the JSON node "data.choices" should have 0 elements

  Scenario: I create a ticket with custom fields
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket w/ custom fields",
  "fields": {
    "~text_field~": "text field value",
    "~textarea_field~": "textarea field value",
    "~date_field~": "2015-10-11",
    "~datetime_field~": "2016-01-02 15:30:42",
    "~single_choice_field~": ["~single_choice_v2_field~"],
    "~checkbox_group_field~": ["~checkbox_group_v1_field~", "~checkbox_group_v3_field~"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.fields.{text_field}.value" should be equal to "text field value"
    And the JSON node "data.fields.{textarea_field}.value" should be equal to "textarea field value"
    And the JSON node "data.fields.{date_field}.value" should be equal to "2015-10-11T00:00:00+0000"
    And the JSON node "data.fields.{datetime_field}.value" should be equal to "2016-01-02T15:30:42+0000"
    And the JSON node "data.fields.{single_choice_field}.detail.{single_choice_v2_field}.title" should be equal to "Single Choice v2"
    And the JSON node "data.fields.{single_choice_field}.detail.{single_choice_v1_field}.title" should not exist
    And the JSON node "data.fields.{single_choice_field}.detail.{single_choice_v3_field}.title" should not exist
    And the JSON node "data.fields.{checkbox_group_field}.detail.{checkbox_group_v1_field}.title" should be equal to "Multi Choice v1"
    And the JSON node "data.fields.{checkbox_group_field}.detail.{checkbox_group_v3_field}.title" should be equal to "Multi Choice v3"
    And the JSON node "data.fields.{checkbox_group_field}.detail.{checkbox_group_v2_field}" should not exist

  Scenario: I modify a ticket with custom fields
    Given I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket w/ custom fields",
  "fields": {
    "~text_field~": "text field value",
    "~textarea_field~": "textarea field value",
    "~date_field~": "2015-10-11",
    "~datetime_field~": "2016-01-02 15:30:42",
    "~single_choice_field~": ["~single_choice_v2_field~"],
    "~checkbox_group_field~": ["~checkbox_group_v1_field~", "~checkbox_group_v3_field~"]
  }
}
    """
    And the response status code should be 201
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Modified subject",
  "fields": {
    "~text_field~": "modified text",
    "~textarea_field~": "modified textarea",
    "~date_field~": "2015-12-11",
    "~datetime_field~": "2016-02-01 15:30:42",
    "~single_choice_field~": ["~single_choice_v1_field~"],
    "~checkbox_group_field~": ["~checkbox_group_v1_field~", "~checkbox_group_v2_field~"]
  }
}
    """
    And the response status code should be 201
    And the JSON node "data.subject" should be equal to "Modified subject"
    And the JSON node "data.fields.{text_field}.value" should be equal to "modified text"
    And the JSON node "data.fields.{textarea_field}.value" should be equal to "modified textarea"
    And the JSON node "data.fields.{date_field}.value" should be equal to "2015-12-11T00:00:00+0000"
    And the JSON node "data.fields.{datetime_field}.value" should be equal to "2016-02-01T15:30:42+0000"
    And the JSON node "data.fields.{single_choice_field}.detail.{single_choice_v1_field}.title" should be equal to "Single Choice v1"
    And the JSON node "data.fields.{single_choice_field}.detail.{single_choice_v2_field}.title" should not exist
    And the JSON node "data.fields.{single_choice_field}.detail.{single_choice_v3_field}.title" should not exist
    And the JSON node "data.fields.{checkbox_group_field}.detail.{checkbox_group_v1_field}.title" should be equal to "Multi Choice v1"
    And the JSON node "data.fields.{checkbox_group_field}.detail.{checkbox_group_v2_field}.title" should be equal to "Multi Choice v2"
    And the JSON node "data.fields.{checkbox_group_field}.detail.{checkbox_group_v3_field}" should not exist

  Scenario: I try to modify a custom field via API
    Given I send a PUT request to "/api/v2/ticket_custom_fields/{text_field}"
    Then the response status code should be 204

  Scenario: I try to delete a custom field via API
    Given I send a DELETE request to "/api/v2/ticket_custom_fields/{text_field}"
    Then the response status code should be 200
