@new
Feature: Custom fields naughty tags replace

  Background:
    Given I'm authenticated as admin

  Scenario: I check xss replace on tickets form
    Given only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
    And only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |

    When I send a PUT request to "/api/v2/tickets/{t1}" with body:
    """
{
  "fields": {
    "~f1~": "<html><body class=my-class><div>my value</div></body></html>"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should be equal to the string "[html][body class=my-class]<div>my value</div>[/body][/html]"
