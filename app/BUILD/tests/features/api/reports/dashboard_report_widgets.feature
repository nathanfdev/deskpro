@new
Feature: /dashboard_report_widgets endpoint

  Background:
    Given I'm authenticated as agent
    And the setting "beta_features.new_reports" is set to 1
    And only the following ReportWidget records exist:
      | #  | Display Types | Title           | Query                          | Is Custom | Variables                                            |
      | w1 | ["table"]     | Built-in report | SELECT tickets.id FROM tickets | 0         | [{"name":"date","type":"dates","value":"last_year"}] |
      | w2 | ["table"]     | Custom report   | SELECT tickets.id FROM tickets | 1         | []                                                   |
    And only the following ReportDashboard records exist:
      | #  | Title       | Is Default |
      | d1 | Dashboard 1 | 1          |
      | d2 | Dashboard 2 | 0          |
      | d3 | Dashboard 3 | 0          |
      | d4 | Dashboard 4 | 1          |
    And only the following ReportDashboardPermission records exist:
      | #  | Dashboard | Person  | Name |
      | p1 | {d1}      | {agent} | view |
      | p2 | {d2}      | {agent} | full |
      | p3 | {d4}      | {agent} | full |
    And only the following ReportDashboardReport records exist:
      | #  | Title    | Dashboard | Variables |
      | r1 | Report 1 | {d1}      | NULL      |
      | r2 | Report 2 | {d1}      | NULL      |
      | r3 | Report 3 | {d2}      | NULL      |
      | r4 | Report 4 | {d3}      | NULL      |
      | r5 | Report 5 | {d4}      | NULL      |
    And only the following ReportDashboardWidget records exist:
      | #   | Title    | Type  | Report | Widget | Size X | Size Y | Row | Col |
      | dw1 | Widget 1 | table | {r1}   | {w1}   | 10     | 15     | 1   | 11  |
      | dw2 | Widget 2 | table | {r1}   | {w1}   | 20     | 25     | 2   | 12  |
      | dw3 | Widget 3 | table | {r2}   | {w1}   | 30     | 35     | 3   | 13  |
      | dw4 | Widget 4 | table | {r3}   | {w2}   | 40     | 45     | 4   | 14  |
      | dw5 | Widget 5 | table | {r4}   | {w2}   | 50     | 55     | 5   | 15  |
      | dw6 | Widget 6 | table | {r5}   | {w2}   | 60     | 65     | 6   | 16  |

  Scenario: I try to retrieve report widget of non-permitted dashboard
    When I send a GET request to "/api/v2/dashboard_reports/{r4}/widgets"
    Then the response status code should be 403

  Scenario: I retrieve dashboard report widgets
    When I send a GET request to "/api/v2/dashboard_reports/{r1}/widgets?order_dir=asc&order_by=id"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{dw1}"
    And the JSON node "data[1].id" should be equal to "{dw2}"

  Scenario: I try to get report widget of non-permitted dashboard
    When I send a GET request to "/api/v2/dashboard_report_widgets/{dw5}"
    Then the response status code should be 403

  Scenario: I get dashboard report widget
    When I send a GET request to "/api/v2/dashboard_report_widgets/{dw1}"
    Then the JSON node "data.id" should be equal to "{dw1}"
    And the JSON node "data.title" should be equal to "Widget 1"
    And the JSON node "data.widget_type" should be equal to "table"
    And the JSON node "data.widget" should be equal to "{w1}"
    And the JSON node "data.size_x" should be equal to 10
    And the JSON node "data.size_y" should be equal to 15
    And the JSON node "data.col" should be equal to 11
    And the JSON node "data.row" should be equal to 1

  Scenario: I try to create a new report widget of non-permitted dashboard
    When I send a POST request to "/api/v2/dashboard_report_widgets" with body:
    """
{
  "title": "My widget",
  "report": ~r4~
}

    """
    Then the response status code should be 400
    And the JSON node "errors.fields.report.errors[0].code" should be equal to "bad_choice"

  Scenario: I try to create a new report widget for default dashboard
    When I send a POST request to "/api/v2/dashboard_report_widgets" with body:
    """
{
  "title": "My widget",
  "report": ~r1~
}

    """
    Then the response status code should be 400
    And the JSON node "errors.fields.report.errors[0].code" should be equal to "bad_choice"

  Scenario: I try to create a new report widget for default dashboard
    When I send a POST request to "/api/v2/dashboard_report_widgets" with body:
    """
{
  "title": "My widget",
  "report": ~r3~,
  "type": "bars",
  "widget": ~w1~,
  "size_x": 100,
  "size_y": 200,
  "row": 20,
  "col": 50,
  "widget_variables": [
    {
      "name": "date",
      "value": "last_month"
    }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "My widget"
    And the JSON node "data.type" should be equal to "bars"
    And the JSON node "data.widget_type" should be equal to "graph"
    And the JSON node "data.widget" should be equal to "{w1}"
    And the JSON node "data.size_x" should be equal to 100
    And the JSON node "data.size_y" should be equal to 200
    And the JSON node "data.row" should be equal to 20
    And the JSON node "data.col" should be equal to 50
    And the JSON node "data.widget_variables" should have 1 element
    And the JSON node "data.widget_variables[0].name" should be equal to "date"
    And the JSON node "data.widget_variables[0].type" should be equal to "dates"
    And the JSON node "data.widget_variables[0].value" should be equal to "last_month"

  Scenario: I try to update a dashboard report widget of non-permitted dashboard
    When I send a PUT request to "/api/v2/dashboard_report_widgets/{dw1}"
    Then the response status code should be 403

  Scenario: I try to update a dashboard report widget of default dashboard
    When I send a PUT request to "/api/v2/dashboard_report_widgets/{dw1}"
    Then the response status code should be 403

  Scenario: I update a dashboard report widget
    When I send a PUT request to "/api/v2/dashboard_report_widgets/{dw4}" with body:
    """
{
  "title": "Edited widget",
  "report": ~r3~,
  "type": "bars",
  "widget": ~w1~,
  "size_x": 100,
  "size_y": 200,
  "row": 20,
  "col": 50,
  "widget_variables": [
    {
      "name": "date",
      "value": "last_month"
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboard_report_widgets/{dw4}"
    Then the JSON node "data.title" should be equal to "Edited widget"
    And the JSON node "data.report" should be equal to "{r3}"
    And the JSON node "data.type" should be equal to "bars"
    And the JSON node "data.widget_type" should be equal to "graph"
    And the JSON node "data.widget" should be equal to "{w1}"
    And the JSON node "data.size_x" should be equal to 100
    And the JSON node "data.size_y" should be equal to 200
    And the JSON node "data.row" should be equal to 20
    And the JSON node "data.col" should be equal to 50
    And the JSON node "data.widget_variables" should have 1 element
    And the JSON node "data.widget_variables[0].name" should be equal to "date"
    And the JSON node "data.widget_variables[0].type" should be equal to "dates"
    And the JSON node "data.widget_variables[0].value" should be equal to "last_month"

  Scenario: I try to delete a dashboard report widget from non-permitted dashboard
    When I send a DELETE request to "/api/v2/dashboard_report_widgets/{dw5}"
    Then the response status code should be 403

  Scenario: I try to delete a dashboard report widget from default dashboard
    When I send a DELETE request to "/api/v2/dashboard_report_widgets/{dw1}"
    Then the response status code should be 403

  Scenario: I delete a dashboard report widget
    When I send a DELETE request to "/api/v2/dashboard_report_widgets/{dw4}"
    Then the response status code should be 200
