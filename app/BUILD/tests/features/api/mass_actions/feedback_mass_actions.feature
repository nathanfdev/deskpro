@new
Feature: /mass_actions/feedback endpoint
  To complete mass actions on feedback list
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And no "Feedback" records exist
    And only the following "FeedbackStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | fsc1 | active      | Collected | 0             |
      | fsc2 | active      | Accepted  | 0             |
      | fsc3 | closed      | Declined  | 0             |
    And only the following "FeedbackCategory" records exist:
      | #   | title      | slug       |
      | fc1 | Feature    | feature    |
      | fc2 | Suggestion | suggestion |
    And only the following "Feedback" records exist:
      | #        | status_category | category | person  | is_reviewed | slug      | title     | content   | status |
      | feedback | {fsc1}          | {fc1}    | {admin} | 0           | feedback1 | Feedback1 | Feedback1 | active |
  Scenario: I set incorrect hidden_status for feedback

    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [~feedback~],
  "params":{"set_hidden_status": "incorrect"}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "set_hidden_status" with value "incorrect" is invalid. Accepted values are: "deleted", "draft", "spam", "unpublished".'

  Scenario: I set incorrect status category for feedback
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [~feedback~],
  "params":{"set_status_category": 1000}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Status category with ID=1000 doesn't exists"

  Scenario: I set incorrect status category for feedback
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [~feedback~],
  "params":{"set_status_category": [~fsc1~,~fsc1~,~fsc3~]}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "set_status_category" with value array is expected to be of type "string" or "int", but is of type "array".'

  Scenario: I set incorrect status category for feedback
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [~feedback~],
  "params":{"set_status_category": "anything"}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "set_status_category" with value "anything" is invalid.'

  Scenario: I try to add incorrect labels to feedback
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [~feedback~],
  "params":{"add_labels": 1}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "add_labels" with value "1" is expected to be of type "array", but is of type "string".'

  Scenario: I try to remove incorrect labels to feedback with ID=1
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [~feedback~],
  "params":{"remove_labels": 1}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "remove_labels" with value "1" is expected to be of type "array", but is of type "string".'

  Scenario: I apply set of actions on feedback
    Given only the following custom feedback fields exist:
      | #    | parent | app_id | sys_name | js_class | has_form_template | has_display_template | title    | description | handler_class                                           | options | is_user_enabled | is_enabled | display_order | default_value | is_agent_field |
      | cdf1 |        |        | cat      |          | 0                 | 0                    | Category | Category    | Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice |         | 1               | 1          | 0             |               | 1              |
      | cdf2 | {cdf1} |        |          |          | 0                 | 0                    | Windows  |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf3 | {cdf1} |        |          |          | 0                 | 0                    | Mac      |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf4 | {cdf1} |        |          |          | 0                 | 0                    | Linux    |             |                                                         |         | 1               | 1          | 0             |               | 1              |
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [~feedback~],
  "params":{
     "set_category": ~cdf2~,
     "set_status_category": ~fsc1~,
     "set_type": ~fc2~,
     "add_labels": ["first", "second"]
  }
}
    """
    Then the response status code should be 200

    When I send a GET request to "/api/v2/feedback/{feedback}"
    Then the response status code should be 200
    And the response should be in JSON
    And print last JSON response
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{feedback}"
    And the JSON node "data.category" should be equal to "{fc2}"
    And the JSON node "data.status_category" should be equal to "{fsc1}"
    And the JSON node "data.fields.{cdf1}.value" should be equal to "1"
    And the JSON node "data.labels[0]" should be equal to "first"
    And the JSON node "data.labels[1]" should be equal to "second"


  Scenario: I apply set of actions on feedback
    Given only the following "LabelDef" records exist:
      | label_type | label  | color | total |
      | feedback   | first  | red   | 0     |
      | feedback   | second | blue  | 0     |
      | feedback   | third  | white | 0     |
    And only the following "LabelFeedback" records exist:
      | feedback   | label  |
      | {feedback} | first  |
      | {feedback} | second |
      | {feedback} | third  |
    When I send a POST request to "/api/v2/mass_actions/feedback" with body:
    """
{
  "ids": [~feedback~],
  "params":{
     "set_of_actions": ["delete"],
     "remove_labels": ["first", "second"]
  }
}
    """
    Then the response status code should be 200

    When I send a GET request to "/api/v2/feedback/{feedback}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{feedback}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "deleted"
    And the JSON node "data.labels[0]" should be equal to "third"
