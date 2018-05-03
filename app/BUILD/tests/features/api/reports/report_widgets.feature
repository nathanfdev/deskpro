@new
Feature: /report_widgets endpoint

  Background:
    Given I'm authenticated as admin
    And the setting "beta_features.new_reports" is set to 1
    And only the following ReportWidget records exist:
      | #  | Display Types | Title           | Query                          | Is Custom |
      | r1 | ["table"]     | Built-in report | SELECT tickets.id FROM tickets | 0         |
      | r2 | ["table"]     | Custom report   | SELECT tickets.id FROM tickets | 1         |

  Scenario: I retrieve a list of report widgets
    When I send a GET request to "/api/v2/report_widgets"
    Then the JSON node "data" should have 2 elements

  Scenario: I get a report widget
    When I send a GET request to "/api/v2/report_widgets/{r1}"
    Then the JSON node "data.id" should be equal to "{r1}"
    And the JSON node "data.display_types[0]" should be equal to "table"
    And the JSON node "data.title" should be equal to "Built-in report"
    And the JSON node "data.is_custom" should be equal to 0

  Scenario: I create a new report widget from a raw query
    When I send a POST request to "/api/v2/report_widgets" with body:
    """
{
  "display_types": ["table"],
  "title": "My widget",
  "input_mode": "dpql",
  "query": "SELECT tickets.id, tickets.ref FROM tickets WHERE tickets.id > 1 SPLIT BY tickets.agent GROUP BY tickets.person WITH ROLLUP ORDER BY tickets.urgency LIMIT 100 OFFSET 50"
}
    """
    Then the response status code should be 201
    And the JSON node "data.display_types[0]" should be equal to "table"
    And the JSON node "data.title" should be equal to "My widget"
    And the JSON node "data.query" should be equal to "SELECT tickets.id, tickets.ref FROM tickets WHERE tickets.id > 1 SPLIT BY tickets.agent GROUP BY tickets.person WITH ROLLUP ORDER BY tickets.urgency LIMIT 100 OFFSET 50"
    And the JSON node "data.query_parts.select" should be equal to "tickets.id, tickets.ref"
    And the JSON node "data.query_parts.from" should be equal to "tickets"
    And the JSON node "data.query_parts.where" should be equal to "tickets.id > 1"
    And the JSON node "data.query_parts.split_by" should be equal to "tickets.agent"
    And the JSON node "data.query_parts.group_by" should be equal to "tickets.person"
    And the JSON node "data.query_parts.order_by" should be equal to "tickets.urgency"
    And the JSON node "data.query_parts.with_rollup" should be equal to 1
    And the JSON node "data.query_parts.limit" should be equal to 100
    And the JSON node "data.query_parts.offset" should be equal to 50
    And the JSON node "data.is_custom" should be equal to 1

  Scenario: I create a new report widget from query parts
    When I send a POST request to "/api/v2/report_widgets" with body:
    """
{
  "display_types": ["table"],
  "title": "My widget",
  "input_mode": "form",
  "query_parts": {
    "select": "tickets.id",
    "from": "tickets",
    "where": "tickets.id > 100",
    "limit": 100,
    "offset": 200,
    "group_by": "tickets.agent",
    "split_by": "tickets.person",
    "order_by": "tickets.ref",
    "with_rollup": 1
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.query" should contain "SELECT tickets.id"
    And the JSON node "data.query" should contain "FROM tickets"
    And the JSON node "data.query" should contain "WHERE tickets.id > 100"
    And the JSON node "data.query" should contain "SPLIT BY tickets.person"
    And the JSON node "data.query" should contain "GROUP BY tickets.agent"
    And the JSON node "data.query" should contain "WITH ROLLUP"
    And the JSON node "data.query" should contain "ORDER BY tickets.ref"
    And the JSON node "data.query" should contain "LIMIT 100 OFFSET 200"
    And the JSON node "data.query_parts.select" should be equal to "tickets.id"
    And the JSON node "data.query_parts.from" should be equal to "tickets"
    And the JSON node "data.query_parts.where" should be equal to "tickets.id > 100"
    And the JSON node "data.query_parts.split_by" should be equal to "tickets.person"
    And the JSON node "data.query_parts.group_by" should be equal to "tickets.agent"
    And the JSON node "data.query_parts.order_by" should be equal to "tickets.ref"
    And the JSON node "data.query_parts.with_rollup" should be equal to 1
    And the JSON node "data.query_parts.limit" should be equal to 100
    And the JSON node "data.query_parts.offset" should be equal to 200

  Scenario: I check raw query validation
    When I send a POST request to "/api/v2/report_widgets" with body:
    """
{
  "display_types": ["table"],
  "title": "My widget",
  "input_mode": "dpql",
  "query": "SELECT * FROM tickets"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.query.errors[0].code" should be equal to "invalid_dpql_query"

  Scenario: I check empty raw query validation
    When I send a POST request to "/api/v2/report_widgets" with body:
    """
{
  "display_types": ["table"],
  "title": "My widget",
  "input_mode": "dpql"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.query.errors[0].code" should be equal to "required"

  Scenario: I check query parts validation
    When I send a POST request to "/api/v2/report_widgets" with body:
    """
{
  "display_types": ["table"],
  "title": "My widget",
  "input_mode": "form",
  "query_parts": {
    "select": "*",
    "from": "tickets",
    "where": "tickets.id > 100"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.query_parts.errors[0].code" should be equal to "invalid_dpql_query"

  Scenario: I check empty query parts validation
    When I send a POST request to "/api/v2/report_widgets" with body:
    """
{
  "display_types": ["table"],
  "title": "My widget",
  "input_mode": "form"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.query_parts.errors[0].code" should be equal to "required"

  Scenario: I update a report widget
    When I send a PUT request to "/api/v2/report_widgets/{r2}" with body:
    """
{
  "title": "Updated title"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/report_widgets/{r2}"
    And the JSON node "data.title" should be equal to "Updated title"

  Scenario: I try to update a built-in report
    When I send a PUT request to "/api/v2/report_widgets/{r1}" with body:
    """
{
  "title": "Updated title"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"

  Scenario Outline: I change 'display types' of a built-in report
    When I send a PUT request to "/api/v2/report_widgets/{r1}" with body:
    """
{
  "display_types": ["<type>"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/report_widgets/{r1}"
    And the JSON node "data.display_types[0]" should be equal to "<type>"

    Examples:
      | type         |
      | pie          |
      | table        |
      | simple_bars  |
      | simple_stat |
      | simple_area  |
      | simple_lines |
      | gauge        |
      | bubble       |

  Scenario: I try to delete a built-in report
    When I send a DELETE request to "/api/v2/report_widgets/{r1}"
    Then the response status code should be 400

  Scenario: I delete a custom report
    When I send a DELETE request to "/api/v2/report_widgets/{r2}"
    Then the response status code should be 200

  Scenario: I update a report widget
    When I send a PUT request to "/api/v2/report_widgets/{r2}" with body:
    """
{
  "labels": ["label 1", "Label 2", "Person", "Feedback"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/report_widgets/{r2}"
    Then the JSON node "data.labels" should have 4 elements
    And the JSON node "data.labels[0]" should be equal to "Label 1"
    And the JSON node "data.labels[1]" should be equal to "Label 2"
    And the JSON node "data.labels[2]" should be equal to "Person"
    And the JSON node "data.labels[3]" should be equal to "Feedback"


    Scenario: I fetch group-params which would be used as vars
      When I send a GET request to "/api/v2/report_widgets/group-params"
      Then the JSON node "" should have 13 elements
      And the JSON node "fields" should exist
      And the JSON node "dates" should exist
      And the JSON node "statuses" should exist
      And the JSON node "orders" should exist
      And the JSON node "values" should exist
      And the JSON node "values.agent" should exist
      And the JSON node "values.team" should exist
      And the JSON node "values.department" should exist
      And the JSON node "values.organization" should exist