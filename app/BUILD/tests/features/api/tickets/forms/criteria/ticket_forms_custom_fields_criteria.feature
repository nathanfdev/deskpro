@new
Feature: /ticket_forms endpoint
  I want to check fields criteria

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
    And only the following custom ticket fields exist:
      | #                       | Type           | Title               | Parent                 |
      | text_field              | text           | Text field          |                        |
      | textarea_field          | textarea       | Textarea field      |                        |
      | date_field              | date           | Date field          |                        |
      | single_choice_field     | single_choice  | Single Choice field |                        |
      | single_choice_v1_field  |                | Single Choice v1    | {single_choice_field}  |
      | single_choice_v2_field  |                | Single Choice v2    | {single_choice_field}  |
      | single_choice_v3_field  |                | Single Choice v3    | {single_choice_field}  |

  Scenario: I check field is not present on the form on custom fields criteria
    Given the only default ticket layout exists with fields:
      | agent_layout                       | agent_layout_options                                                                         |
      | ticket_field_{text_field}          | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{date_field}          | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{single_choice_field} | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{textarea_field}      | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckTicketField~text_field~","op":"contains","options":{"value":"cowabunga","type_name":"text","field_id":"~text_field~"}},{"type":"CheckTicketField~single_choice_field~","op":"not","options":{"value":["~single_choice_v2_field~"],"type_name":"choice","field_id":"~single_choice_field~"}},{"type":"CheckTicketField~date_field~","op":"lte","options":{"date1":1451606434,"type_name":"date","value":"date","field_id":"~date_field~"}}]}} |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~textarea_field~": "some text"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.fields.errors[0].message" should contain "~textarea_field~"

  Scenario: I check field is present on the form on custom text field criteria
    Given the only default ticket layout exists with fields:
      | agent_layout                       | agent_layout_options                                                                         |
      | ticket_field_{text_field}          | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{date_field}          | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{single_choice_field} | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{textarea_field}      | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckTicketField~text_field~","op":"contains","options":{"value":"cowabunga","type_name":"text","field_id":"~text_field~"}}]}} |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~text_field~": "Text with cowabunga keyword",
    "~textarea_field~": "some text"
  }
}
    """
    Then the response status code should be 204
    And the JSON node "errors.fields" should not exist

  Scenario: I check field is present on the form on custom date field criteria
    Given the only default ticket layout exists with fields:
      | agent_layout                       | agent_layout_options                                                                         |
      | ticket_field_{text_field}          | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{date_field}          | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{single_choice_field} | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{textarea_field}      | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckTicketField~date_field~","op":"lte","options":{"date1":1451606434,"type_name":"date","value":"date","field_id":"~date_field~"}}]}} |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~date_field~": "2015-07-07",
    "~textarea_field~": "some text"
  }
}
    """
    Then the response status code should be 204
    And the JSON node "errors.fields" should not exist

  Scenario: I check field is present on the form on custom choice field criteria
    Given the only default ticket layout exists with fields:
      | agent_layout                       | agent_layout_options                                                                         |
      | ticket_field_{text_field}          | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{date_field}          | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{single_choice_field} | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null} |
      | ticket_field_{textarea_field}      | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckTicketField~single_choice_field~","op":"not","options":{"value":["~single_choice_v2_field~"],"type_name":"choice","field_id":"~single_choice_field~"}}]}} |
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~single_choice_field~": [~single_choice_v1_field~],
    "~textarea_field~": "some text"
  }
}
    """
    Then the response status code should be 204
    And the JSON node "errors.fields" should not exist

