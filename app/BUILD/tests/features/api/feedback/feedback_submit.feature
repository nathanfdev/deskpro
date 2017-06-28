@new
Feature: /feedback endpoint
  I want to submit feedback

  Background:
    Given I'm authenticated as admin
    And only the following "FeedbackStatusCategory" records exist:
      | #    | status_type | title     | display_order |
      | fsc1 | active      | Collected | 0             |
      | fsc2 | active      | Accepted  | 0             |
      | fsc3 | closed      | Declined  | 0             |
      | fsc4 | closed      | Spam      | 0             |
    And only the following custom feedback fields exist:
      | #  | Type | Title      |
      | cf1 | text | Text field |
      | cf2 | text | Text field |

  Scenario Outline: I create feedback w/ status category
    When I send a POST request to "/api/v2/feedback" with body:
    """
{
  "title": "My feedback",
  "content": "My feedback content",
  "status": "<status>",
  "status_category": <status_category>
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to the string "My feedback"
    And the JSON node "data.content" should be equal to the string "My feedback content"
    And the JSON node "data.status" should be equal to the string "<status>"
    And the JSON node "data.status_category" should be equal to "<status_category>"
    And the JSON node "data.person" should be equal to "{admin}"

    Examples:
      | status | status_category |
      | active | ~fsc2~          |
      | closed | ~fsc4~          |

  Scenario: I create feedback w/ hidden status
    When I send a POST request to "/api/v2/feedback" with body:
    """
{
  "title": "My feedback",
  "content": "My feedback content",
  "status": "hidden",
  "hidden_status": "spam"
}
    """
    Then the response status code should be 201
    And the JSON node "data.status" should be equal to the string "hidden"
    And the JSON node "data.hidden_status" should be equal to "spam"

  Scenario: I create feedback w/ custom data
    When I send a POST request to "/api/v2/feedback" with body:
    """
{
  "title": "My feedback",
  "content": "My feedback content",
  "status": "hidden",
  "hidden_status": "unpublished",
  "fields": {
    "~cf1~": "field 1 text",
    "~cf2~": "field 2 text"
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.fields.~cf1~.value" should be equal to the string "field 1 text"
    And the JSON node "data.fields.~cf2~.value" should be equal to the string "field 2 text"

  Scenario: I create feedback w/ labels
    When I send a POST request to "/api/v2/feedback" with body:
    """
{
  "title": "My feedback",
  "content": "My feedback content",
  "status": "hidden",
  "hidden_status": "unpublished",
  "labels": ["label 1", "label 2"]
}
    """
    Then the response status code should be 201
    And the JSON node "data.labels[0]" should be equal to the string "label 1"
    And the JSON node "data.labels[1]" should be equal to the string "label 2"

  Scenario: I edit existing feedback
    Given only the following Feedback records exist:
      | #  | Person  | Title      | Content            |
      | f1 | {admin} | Feedback 1 | Feedback content 1 |

    When I send a PUT request to "/api/v2/feedback/{f1}" with body:
    """
{
  "title": "My edited title",
  "content": "My edited content"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/feedback/{f1}"
    Then the response status code should be 200
    And the JSON node "data.title" should be equal to the string "My edited title"
    And the JSON node "data.content" should be equal to the string "My edited content"
