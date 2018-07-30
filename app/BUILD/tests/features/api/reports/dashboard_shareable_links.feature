@new
Feature: /dashboards/{id}/shareable_links endpoint

  Background:
    Given I'm authenticated as admin
    And the setting "beta_features.new_reports" is set to 1
    And only the following ReportDashboard records exist:
      | #  | Title       | Is Default | Is Agent |
      | d1 | Dashboard 1 | 1          | 0        |
      | d2 | Dashboard 2 | 0          | 1        |
    And only the following ReportDashboardReport records exist:
      | #  | Title    | Dashboard | Variables                                             |
      | r1 | Report 1 | {d1}      | [{"name":"date","type":"dates","value":"last_month"}] |
      | r2 | Report 2 | {d1}      | NULL                                                  |
      | r3 | Report 3 | {d2}      | [{"name":"date","type":"dates","value":"last_month"}] |
    And only the following ReportDashboardShareableLink records exist:
      | #  | Title  | Dashboard | Auth Code |
      | l1 | Link 1 | {d1}      | AAAAAAAAA |
      | l2 | Link 2 | {d1}      | BBBBBBBBB |
      | l3 | Link 3 | {d2}      | CCCCCCCCC |
    And only the following ReportDashboardShareableShortUrl records exist:
      | #  | Auth Code | Shareable Link | Date Created        | Date Expire |
      | u1 | AAAAA     | {l1}           | 2018-05-24 00:00:00 | NOW()+1day  |

  Scenario: I retrieve a list of dashboard links
    When I send a GET request to "/api/v2/dashboards/{d1}/shareable_links?order_dir=asc&order_by=id"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{l1}"
    And the JSON node "data[1].id" should be equal to "{l2}"

  Scenario: I get a dashboard link
    When I send a GET request to "/api/v2/dashboard_shareable_links/{l1}"
    Then the JSON node "data.id" should be equal to "{l1}"
    And the JSON node "data.title" should be equal to "Link 1"
    And the JSON node "data.short_url.id" should be equal to "{u1}"
    And the JSON node "data.short_url.auth_code" should be equal to "AAAAA"
    And the JSON node "data.short_url.date_created" should be equal to "2018-05-24T00:00:00+0000"
    And the JSON node "data.short_url.date_expire" should exist

  Scenario: I create a dashboard link
    When I send a POST request to "/api/v2/dashboard_shareable_links" with body:
    """
{
  "title": "My link",
  "dashboard": ~d1~,
  "default_report": ~r2~
}
    """
    Then the response status code should be 201
    And the JSON node "data.title" should be equal to "My link"
    And the JSON node "data.dashboard" should be equal to "{d1}"
    And the JSON node "data.default_report" should be equal to "{r2}"
    And the JSON node "data.who_can_use" should be equal to "anyone"

  Scenario: I check default report validation if report doesn't belong to this dashboard
    When I send a POST request to "/api/v2/dashboard_shareable_links" with body:
    """
{
  "title": "My link",
  "dashboard": ~d1~,
  "default_report": ~r3~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.default_report.errors[0].code" should be equal to "bad_choice"

  Scenario: I update a dashboard link
    When I send a PUT request to "/api/v2/dashboard_shareable_links/{l2}" with body:
    """
{
  "title": "My updated link",
  "who_can_use": "whitelist",
  "ip_whitelist": ["127.0.0.1"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboard_shareable_links/{l2}"
    Then the JSON node "data.title" should be equal to "My updated link"
    And the JSON node "data.who_can_use" should be equal to "whitelist"
    And the JSON node "data.ip_whitelist" should have 1 element
    And the JSON node "data.ip_whitelist[0]" should be equal to "127.0.0.1"

  Scenario: I send IP whitelist as string
    When I send a PUT request to "/api/v2/dashboard_shareable_links/{l2}" with body:
    """
{
  "who_can_use": "whitelist",
  "ip_whitelist": "127.0.0.1"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/dashboard_shareable_links/{l2}"
    Then the JSON node "data.who_can_use" should be equal to "whitelist"
    And the JSON node "data.ip_whitelist" should have 1 element
    And the JSON node "data.ip_whitelist[0]" should be equal to "127.0.0.1"

  Scenario: I delete a dashboard link
    When I send a DELETE request to "/api/v2/dashboard_shareable_links/{l2}"
    Then the response status code should be 200

  Scenario: I create short url
    When I send a POST request to "/api/v2/dashboard_shareable_links/{l2}/create_short_url"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/dashboard_shareable_links/{l1}"
    Then the JSON node "data.id" should be equal to "{l1}"
    And the JSON node "data.short_url" should exist

  Scenario: I check IP whitelist validation
    When I send a PUT request to "/api/v2/dashboard_shareable_links/{l2}" with body:
    """
{
  "who_can_use": "whitelist",
  "ip_whitelist": "127.0.0"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.ip_whitelist.fields.ip_whitelist_0.errors[0].code" should be equal to "invalid_ip"
