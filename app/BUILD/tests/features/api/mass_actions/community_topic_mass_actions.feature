@new
Feature: /mass_actions/community_topics endpoint
  To complete mass actions on community topics list
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And no "CommunityTopic" records exist
    And only the following "CommunityTopicStatusCategory" records exist:
      | #     | status_type | title     | display_order |
      | ctsc1 | active      | Collected | 0             |
      | ctsc2 | active      | Accepted  | 0             |
      | ctsc3 | closed      | Declined  | 0             |
    And only the following "CommunityChannel" records exist:
      | #   | title      | slug       |
      | cc1 | Feature    | feature    |
      | cc2 | Suggestion | suggestion |
    And only the following "CommunityTopic" records exist:
      | #     | status_category | category | person  | is_reviewed | slug   | title  | content | status |
      | topic | {ctsc1}         | {cc1}    | {admin} | 0           | topic1 | Topic1 | Topic1  | active |

  Scenario: I set incorrect hidden_status for topic
    When I send a POST request to "/api/v2/mass_actions/community_topics" with body:
    """
{
  "ids": [~topic~],
  "params":{"set_hidden_status": "incorrect"}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.set_hidden_status.errors[0].code" should be equal to "bad_choice"

  Scenario Outline: I set incorrect status category for topic
    When I send a POST request to "/api/v2/mass_actions/community_topics" with body:
    """
{
  "ids": [~topic~],
  "params":{"set_status_category": <value>}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.set_status_category.errors[0].code" should be equal to "bad_choice"

    Examples:
      | value      |
      | -1         |
      | "anything" |

  Scenario: I set incorrect status category for topic
    When I send a POST request to "/api/v2/mass_actions/community_topics" with body:
    """
{
  "ids": [~topic~],
  "params":{"set_status_category": [~ctsc1~,~ctsc1~,~ctsc3~]}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.set_status_category.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I try to add incorrect labels to topic
    When I send a POST request to "/api/v2/mass_actions/community_topics" with body:
    """
{
  "ids": [~topic~],
  "params":{"add_labels": 1}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.add_labels.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I try to remove incorrect labels to topic with ID=1
    When I send a POST request to "/api/v2/mass_actions/community_topics" with body:
    """
{
  "ids": [~topic~],
  "params":{"remove_labels": 1}
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.params.fields.remove_labels.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I apply set of actions on topic
    Given only the following custom community fields exist:
      | #    | parent | app_id | sys_name | js_class | has_form_template | has_display_template | title    | description | handler_class                                           | options | is_user_enabled | is_enabled | display_order | default_value | is_agent_field |
      | cdf1 |        |        | cat      |          | 0                 | 0                    | Category | Category    | Application\\\DeskPRO\\\CustomFields\\\Handler\\\Choice |         | 1               | 1          | 0             |               | 1              |
      | cdf2 | {cdf1} |        |          |          | 0                 | 0                    | Windows  |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf3 | {cdf1} |        |          |          | 0                 | 0                    | Mac      |             |                                                         |         | 1               | 1          | 0             |               | 1              |
      | cdf4 | {cdf1} |        |          |          | 0                 | 0                    | Linux    |             |                                                         |         | 1               | 1          | 0             |               | 1              |
    When I send a POST request to "/api/v2/mass_actions/community_topics" with body:
    """
{
  "ids": [~topic~],
  "params":{
     "set_category": ~cdf2~,
     "set_status_category": ~ctsc1~,
     "set_type": ~cc2~,
     "add_labels": ["first", "second"]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/community_topics/{topic}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{topic}"
    And the JSON node "data.category" should be equal to "{cc2}"
    And the JSON node "data.status_category" should be equal to "{ctsc1}"
    And the JSON node "data.fields.{cdf1}.value" should be equal to "1"
    And the JSON node "data.labels[0]" should be equal to "first"
    And the JSON node "data.labels[1]" should be equal to "second"

  Scenario: I apply set of actions on topic
    Given only the following "LabelDef" records exist:
      | label_type | label  | color | total |
      | community  | first  | red   | 0     |
      | community  | second | blue  | 0     |
      | community  | third  | white | 0     |
    And only the following "LabelCommunityTopic" records exist:
      | topic       | label  |
      | {community} | first  |
      | {community} | second |
      | {community} | third  |
    When I send a POST request to "/api/v2/mass_actions/community_topics" with body:
    """
{
  "ids": [~topic~],
  "params":{
     "set_of_actions": ["delete"],
     "remove_labels": ["first", "second"]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/community_topics/{topic}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{topic}"
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "deleted"
    And the JSON node "data.labels[0]" should be equal to "third"
