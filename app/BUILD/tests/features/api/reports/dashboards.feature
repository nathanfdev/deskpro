@new
Feature: /dashboards endpoint

  Background:
    Given I'm authenticated as admin
    Given I'm authenticated as agent
    And "agent_2@deskpro.dev" agent exists
    And "admin_2@deskpro.dev" admin exists
    And the setting "beta_features.new_reports" is set to 1
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
      | p4 | {d1}      | {admin} | full |
    And only the following ReportDashboardReport records exist:
      | #  | Title    | Dashboard | Variables                                             |
      | r1 | Report 1 | {d1}      | [{"name":"date","type":"dates","value":"last_month"}] |
      | r2 | Report 2 | {d1}      | NULL                                                  |
      | r3 | Report 3 | {d2}      | NULL                                                  |

  Scenario: I retrieve a list of report dashboards
    When I send a GET request to "/api/v2/dashboards?order_dir=asc&order_by=id"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{d1}"
    And the JSON node "data[1].id" should be equal to "{d2}"
    And the JSON node "data[2].id" should be equal to "{d4}"

  Scenario: I get a report dashboard
    When I send a GET request to "/api/v2/dashboards/{d1}"
    Then the JSON node "data.id" should be equal to "{d1}"
    And the JSON node "data.title" should be equal to "Dashboard 1"
    And the JSON node "data.is_default" should be equal to 1
    And the JSON node "data.permissions" should have 2 elements
    And the JSON node "data.permissions[0].id" should not exist
    And the JSON node "data.permissions[0].person" should be equal to "{agent}"
    And the JSON node "data.permissions[0].name" should be equal to "view"

  Scenario: I try to get a report dashboard w/o permissions
    When I send a GET request to "/api/v2/dashboards/{d3}"
    Then the response status code should be 403

  Scenario: I create a new report dashboard
    When I send a POST request to "/api/v2/dashboards" with body:
    """
{
  "title": "New Dashboard"
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "New Dashboard"
    And the JSON node "data.is_default" should be equal to 0
    And the JSON node "data.permissions" should have 1 element
    And the JSON node "data.permissions[0].person" should be equal to "{agent}"
    And the JSON node "data.permissions[0].name" should be equal to "full"

  Scenario: I create a new report with permissions
    When I send a POST request to "/api/v2/dashboards" with body:
    """
{
  "title": "New Dashboard",
  "permissions": [
    {
      "name": "full",
      "person": ~agent~
    },
    {
      "name": "view",
      "person": ~agent_2@deskpro.dev~
    }
  ]
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "New Dashboard"
    And the JSON node "data.is_default" should be equal to 0
    And the JSON node "data.permissions" should have 2 elements
    And the JSON node "data.permissions[0].person" should be equal to "{agent}"
    And the JSON node "data.permissions[0].name" should be equal to "full"
    And the JSON node "data.permissions[1].person" should be equal to "{agent_2@deskpro.dev}"
    And the JSON node "data.permissions[1].name" should be equal to "view"

  Scenario: I create a new dashboard w/ tabs
    When I send a POST request to "/api/v2/dashboards" with body:
    """
{
  "title": "New Dashboard",
  "reports": [
    {
      "title": "Tab 1"
    },
    {
      "title": "Tab 2"
    }
  ]
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/dashboards/{lastCreatedId}/reports?order_dir=asc&order_by=id"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to "Tab 1"
    And the JSON node "data[1].title" should be equal to "Tab 2"

  Scenario: I try to update dashboard w/o permissions
    When I send a PUT request to "/api/v2/dashboards/{d1}" with body:
    """
{
  "title": "Updated Dashboard"
}
    """
    Then the response status code should be 403

  Scenario: I update dashboard
    When I send a PUT request to "/api/v2/dashboards/{d2}" with body:
    """
{
  "title": "Updated Dashboard",
  "permissions": [
    {
      "name": "full",
      "person": ~agent_2@deskpro.dev~
    },
    {
      "name": "view",
      "person": ~agent~
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboards/{d2}"
    Then the JSON node "data.title" should be equal to "Updated Dashboard"
    And the JSON node "data.is_default" should be equal to 0
    And the JSON node "data.permissions" should have 2 elements
    And the JSON node "data.permissions[0].person" should be equal to "{agent_2@deskpro.dev}"
    And the JSON node "data.permissions[0].name" should be equal to "full"
    And the JSON node "data.permissions[1].person" should be equal to "{agent}"
    And the JSON node "data.permissions[1].name" should be equal to "view"

  Scenario: I update permissions of default report
    When I send a PUT request to "/api/v2/dashboards/{d4}" with body:
    """
{
  "title": "Updated Dashboard",
  "permissions": [
    {
      "name": "view",
      "person": ~agent_2@deskpro.dev~
    },
    {
      "name": "view",
      "person": ~agent~
    }
  ]
}
    """
    Then the response status code should be 204

  Scenario: I update dashboard and trying to set full permissions to agent
    Given I'm authenticated as admin
    When I send a PUT request to "/api/v2/dashboards/{d1}" with body:
    """
{
  "permissions": [
    {
      "name": "full",
      "person": ~admin~
    },
    {
      "name": "full",
      "person": ~agent~
    }
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.permissions.fields.permissions_1.errors[0].code" should be equal to "no_default_dashboard_permission"

  Scenario: I update dashboard and trying to set full permissions to admin
    Given I'm authenticated as admin
    When I send a PUT request to "/api/v2/dashboards/{d1}" with body:
    """
{
  "permissions": [
    {
      "name": "full",
      "person": ~admin~
    },
    {
      "name": "full",
      "person": ~admin_2@deskpro.dev~
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboards/{d1}"
    Then the JSON node "data.permissions" should have 2 elements
    And the JSON node "data.permissions[1].person" should be equal to "~admin_2@deskpro.dev~"

  Scenario: I try to delete dashboard w/o permissions
    When I send a DELETE request to "/api/v2/dashboards/{d1}"
    Then the response status code should be 403

  Scenario: I try to delete default dashboard
    When I send a DELETE request to "/api/v2/dashboards/{d4}"
    Then the response status code should be 403

  Scenario: I delete dashboard
    When I send a DELETE request to "/api/v2/dashboards/{d2}"
    Then the response status code should be 200

  Scenario: I try to clone a report dashboard w/o permissions
    When I send a POST request to "/api/v2/dashboards/{d3}/clone"
    Then the response status code should be 403

  Scenario: I clone dashboard
    When I send a POST request to "/api/v2/dashboards/{d1}/clone"
    Then the response status code should be 201

    Then the JSON node "data.title" should be equal to "Dashboard 1 (copy)"
    And the JSON node "data.is_default" should be equal to 0
    And the JSON node "data.permissions" should have 2 element
    And the JSON node "data.permissions[0].name" should be equal to "view"
    And the JSON node "data.permissions[0].person" should be equal to "{agent}"

    When I send a GET request to "/api/v2/dashboards/{lastCreatedId}/reports?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].title" should be equal to "Report 1 (copy)"
    And the JSON node "data[1].title" should be equal to "Report 2 (copy)"

  Scenario: I sideload reports
    When I send a GET request to "/api/v2/dashboards?order_dir=asc&order_by=id&include=reports"
    Then the JSON node "linked.reports" should have 3 elements
    And the JSON node "linked.reports.{d1}" should have 2 elements
    And the JSON node "linked.reports.{d2}" should have 1 element
    And the JSON node "linked.reports.{d4}" should have 0 elements

  Scenario: I add dashboard reports
    When I send a PUT request to "/api/v2/dashboards/{d2}" with body:
    """
{
  "reports": [
    {
      "clone_id": "~r1~"
    },
    {
      "title": "New report"
    },
    {
      "id": "~r3~"
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboards/{d2}/reports?order_dir=asc&order_by=id"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].title" should be equal to "Report 3"
    And the JSON node "data[1].title" should be equal to "Report 1 (copy)"
    And the JSON node "data[2].title" should be equal to "New report"

  Scenario: I change dashboard reports
    When I send a PUT request to "/api/v2/dashboards/{d2}" with body:
    """
{
  "reports": [
    {
      "title": "New report"
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboards/{d2}/reports?order_dir=asc&order_by=id"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "New report"

  Scenario: I try to set non-existing report
    When I send a PUT request to "/api/v2/dashboards/{d2}" with body:
    """
{
  "reports": [
    {
      "id": -1
    }
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.reports.fields.reports_0.fields.id.errors[0].code" should be equal to "bad_choice"

  Scenario: I try to clone non-existing report
  Scenario: I try to set non-existing report
    When I send a PUT request to "/api/v2/dashboards/{d2}" with body:
    """
{
  "reports": [
    {
      "clone_id": -1
    }
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.reports.fields.reports_0.fields.clone_id.errors[0].code" should be equal to "bad_choice"

  Scenario: I set agent dashboard
    When I send a PUT request to "/api/v2/dashboards/{d2}" with body:
    """
{
  "is_agent": true
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboards/{d2}"
    And the JSON node "data.is_agent" should be equal to 1
