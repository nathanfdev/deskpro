@new
Feature: Brand Settings Setup

  Background:
    Given I'm authenticated as admin

  Scenario: I get default brand settings
    When I send a GET request to "/api/v2/settings/brands/1/portal/general"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I update default brand settings
    When I send a POST request to "/api/v2/settings/brands/1/portal/general" with body:
    """
{
  "brand": "1",
  "site_name":"Test site",
  "site_url":"http://testsite.com",
  "apps_feedback":true,
  "apps_kb":true,
  "apps_news":true,
  "apps_downloads":true,
  "iface_portal":true,
  "iface_widget":true,
  "show_ratings":true,
  "show_ratings_min_votes":6,
  "publish_comments":true
}
    """
    Then the response should be in JSON
    And the response status code should be 204