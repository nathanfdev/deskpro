@new
Feature: /dashboard_reports endpoint

  Background:
    Given I'm authenticated as agent
    And "agent_2@deskpro.dev" agent exists
    And the setting "beta_features.new_reports" is set to 1
    And the following "AgentTeam" records exist:
      | #  | Name   | Members   |
      | t1 | Team 1 | [{agent}] |
    And only the following ReportDashboard records exist:
      | #  | Title       | Is Default | Is Agent |
      | d1 | Dashboard 1 | 1          | 0        |
      | d2 | Dashboard 2 | 0          | 1        |
      | d3 | Dashboard 3 | 0          | 0        |
      | d4 | Dashboard 4 | 1          | 0        |
    And only the following ReportDashboardPermission records exist:
      | #  | Dashboard | Person  | Name | View All |
      | p1 | {d1}      | {agent} | view | 0        |
      | p2 | {d2}      | {agent} | full | 1        |
      | p3 | {d4}      | {agent} | full | 1        |
    And only the following ReportDashboardReport records exist:
      | #  | Title    | Dashboard | Variables                                             |
      | r1 | Report 1 | {d1}      | [{"name":"date","type":"dates","value":"last_month"}] |
      | r2 | Report 2 | {d1}      | NULL                                                  |
      | r3 | Report 3 | {d2}      | [{"name":"date","type":"dates","value":"last_month"}] |
      | r4 | Report 4 | {d3}      | NULL                                                  |
      | r5 | Report 5 | {d4}      | NULL                                                  |
    And only the following ReportWidget records exist:
      | #  | Display Types | Title           | Query                          | Is Custom |
      | w1 | ["table"]     | Built-in report | SELECT tickets.id FROM tickets | 0         |
    And only the following ReportDashboardWidget records exist:
      | #   | Title    | Type        | Report | Widget | Size X | Size Y | Row | Col |
      | dw1 | Widget 1 | table       | {r3}   | {w1}   | 10     | 15     | 1   | 11  |
      | dw2 | Widget 2 | simple_bars | {r3}   | {w1}   | 20     | 25     | 2   | 12  |

  Scenario: I try to retrieve a list of non-permitted dashboard
    When I send a GET request to "/api/v2/dashboards/{d3}/reports"
    Then the response status code should be 403

  Scenario: I retrieve a list of dashboard's reports
    When I send a GET request to "/api/v2/dashboards/{d1}/reports?order_dir=asc&order_by=id"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{r1}"
    And the JSON node "data[1].id" should be equal to "{r2}"

  Scenario: I try to get a report of non-permitted dashboard
    When I send a GET request to "/api/v2/dashboard_reports/{r4}"
    Then the response status code should be 403

  Scenario: I get a report
    When I send a GET request to "/api/v2/dashboard_reports/{r1}"
    Then the JSON node "data.id" should be equal to "{r1}"
    And the JSON node "data.title" should be equal to "Report 1"
    And the JSON node "data.variables" should have 1 element
    And the JSON node "data.variables[0].name" should be equal to "date"
    And the JSON node "data.variables[0].type" should be equal to "dates"
    And the JSON node "data.variables[0].value" should be equal to "last_month"
    And the JSON node "data.schedule" should be null

  Scenario: I try to create a new report of non-permitted dashboard
    When I send a POST request to "/api/v2/dashboard_reports" with body:
    """
{
  "title": "My report",
  "dashboard": ~d3~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.dashboard.errors[0].code" should be equal to "bad_choice"

  Scenario: I try to create a new report for default dashboard
    When I send a POST request to "/api/v2/dashboard_reports" with body:
    """
{
  "title": "My report",
  "dashboard": ~d1~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.dashboard.errors[0].code" should be equal to "bad_choice"

  Scenario: I create a new report w/o vars
    When I send a POST request to "/api/v2/dashboard_reports" with body:
    """
{
  "title": "My report",
  "dashboard": ~d2~
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "My report"
    And the JSON node "data.variables" should have 0 elements

  Scenario: I create a new report w/ vars
    When I send a POST request to "/api/v2/dashboard_reports" with body:
    """
{
  "title": "My report",
  "dashboard": ~d2~,
  "variables": [
    {
      "name": "date",
      "type": "dates",
      "value": "last_month"
    }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "My report"
    And the JSON node "data.variables" should have 1 element
    And the JSON node "data.variables[0].name" should be equal to "date"
    And the JSON node "data.variables[0].type" should be equal to "dates"
    And the JSON node "data.variables[0].value" should be equal to "last_month"

  Scenario: I try to update a report of non-permitted dashboard
    When I send a PUT request to "/api/v2/dashboard_reports/{r4}"
    Then the response status code should be 403

  Scenario: I update a report
    When I send a PUT request to "/api/v2/dashboard_reports/{r3}" with body:
    """
{
  "title": "Updated report",
  "variables": [
    {
      "name": "date",
      "type": "dates",
      "value": "last_month"
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboard_reports/{r3}"
    Then the JSON node "data.id" should be equal to "{r3}"
    And the JSON node "data.title" should be equal to "Updated report"
    And the JSON node "data.variables" should have 1 element
    And the JSON node "data.variables[0].name" should be equal to "date"
    And the JSON node "data.variables[0].type" should be equal to "dates"
    And the JSON node "data.variables[0].value" should be equal to "last_month"

  Scenario: I try to delete a report of non-permitted dashboard
    When I send a DELETE request to "/api/v2/dashboard_reports/{r4}"
    Then the response status code should be 403

  Scenario: I delete a report
    When I send a DELETE request to "/api/v2/dashboard_reports/{r3}"
    Then the response status code should be 200

  Scenario: I clone report
    When I send a POST request to "/api/v2/dashboard_reports/{r3}/clone"
    Then the response status code should be 201

    Then the JSON node "data.title" should be equal to "Report 3 (copy)"
    And the JSON node "data.variables" should have 1 element
    And the JSON node "data.variables[0].name" should be equal to "date"
    And the JSON node "data.variables[0].type" should be equal to "dates"
    And the JSON node "data.variables[0].value" should be equal to "last_month"

    When I send a GET request to "/api/v2/dashboard_reports/{lastCreatedId}/widgets?order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to "Widget 1"
    And the JSON node "data[0].type" should be equal to "table"
    And the JSON node "data[0].size_x" should be equal to 10
    And the JSON node "data[0].size_y" should be equal to 15
    And the JSON node "data[0].col" should be equal to 11
    And the JSON node "data[0].row" should be equal to 1
    And the JSON node "data[1].title" should be equal to "Widget 2"
    And the JSON node "data[1].type" should be equal to "simple_bars"

  Scenario: I schedule everyday report
    When I send a POST request to "/api/v2/dashboard_reports" with body:
    """
{
  "title": "My report",
  "dashboard": ~d2~,
  "schedule": {
    "frequency": "daily",
    "when": {
      "time": "10:00"
    },
    "send_to": ["email_1@example.com"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.schedule.frequency" should be equal to "daily"
    And the JSON node "data.schedule.when.time" should be equal to "10:00"
    And the JSON node "data.schedule.send_to" should have 1 element
    And the JSON node "data.schedule.send_to[0]" should be equal to "email_1@example.com"

  Scenario: I schedule weekly report
    When I send a POST request to "/api/v2/dashboard_reports" with body:
    """
{
  "title": "My report",
  "dashboard": ~d2~,
  "schedule": {
    "frequency": "weekly",
    "when": {
      "time": "10:00",
      "weekday": "friday"
    },
    "send_to": ["email_1@example.com"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.schedule.frequency" should be equal to "weekly"
    And the JSON node "data.schedule.when.time" should be equal to "10:00"
    And the JSON node "data.schedule.when.weekday" should be equal to "friday"

  Scenario: I schedule monthly report
    When I send a POST request to "/api/v2/dashboard_reports" with body:
    """
{
  "title": "My report",
  "dashboard": ~d2~,
  "schedule": {
    "frequency": "monthly",
    "when": {
      "time": "10:00",
      "monthday": "15"
    },
    "send_to": ["email_1@example.com"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.schedule.frequency" should be equal to "monthly"
    And the JSON node "data.schedule.when.time" should be equal to "10:00"
    And the JSON node "data.schedule.when.monthday" should be equal to 15

  Scenario: I schedule bimonthly report
    When I send a POST request to "/api/v2/dashboard_reports" with body:
    """
{
  "title": "My report",
  "dashboard": ~d2~,
  "schedule": {
    "frequency": "bimonthly",
    "when": {
      "time": "10:00",
      "monthday": "15",
      "monthday2": "last"
    },
    "send_to": ["email_1@example.com"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.schedule.frequency" should be equal to "bimonthly"
    And the JSON node "data.schedule.when.time" should be equal to "10:00"
    And the JSON node "data.schedule.when.monthday" should be equal to 15
    And the JSON node "data.schedule.when.monthday2" should be equal to "last"

  Scenario: I update schedule
    Given only the following ScheduledReport records exist:
      | #  | Report | Person  | Frequency | When Setting      | When Tz |
      | s1 | {r3}   | {agent} | daily     | {"time": "10:00"} | UTC     |

    When I send a PUT request to "/api/v2/dashboard_reports/{r3}" with body:
    """
{
  "schedule": {
    "frequency": "bimonthly",
    "when": {
      "time": "15:00",
      "monthday": "15",
      "monthday2": "last"
    },
    "send_to": ["email_1@example.com"]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboard_reports/{r3}"
    And the JSON node "data.schedule.frequency" should be equal to "bimonthly"
    And the JSON node "data.schedule.when.time" should be equal to "15:00"
    And the JSON node "data.schedule.when.monthday" should be equal to 15
    And the JSON node "data.schedule.when.monthday2" should be equal to "last"

  Scenario: I unset schedule
    Given only the following ScheduledReport records exist:
      | #  | Report | Person  | Frequency | When Setting      | When Tz |
      | s1 | {r3}   | {agent} | daily     | {"time": "10:00"} | UTC     |

    When I send a PUT request to "/api/v2/dashboard_reports/{r3}" with body:
    """
{
  "schedule": null
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboard_reports/{r3}"
    And the JSON node "data.schedule" should be null
