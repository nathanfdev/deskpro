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
  "deskpro_name":"Test site",
  "deskpro_url":"http://testsite.com",
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


  Scenario: I check updated default brand settings
    When I send a GET request to "/api/v2/settings/brands/1/portal/general"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.deskpro_name" should be equal to "Test site"
    And the JSON node "data.deskpro_url" should be equal to "http://testsite.com"
    And the JSON node "data.apps_feedback" should be true
    And the JSON node "data.apps_kb" should be true
    And the JSON node "data.apps_news" should be true
    And the JSON node "data.apps_downloads" should be true
    And the JSON node "data.iface_portal" should be true
    And the JSON node "data.iface_widget" should be true
    And the JSON node "data.show_ratings" should be true
    And the JSON node "data.show_ratings_min_votes" should be equal to "6"
    And the JSON node "data.publish_comments" should be true

  Scenario: I create a new brand and update its settings
    When I send a POST request to "/api/v2/brands" with body:
    """
{
  "name": "Test brand",
  "url": "my.domain.com"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    Then I send a POST request to "/api/v2/settings/brands/{lastCreatedId}/portal/general" with body:
    """
{
  "brand": "~lastCreatedId~",
  "deskpro_name":"Other Brand",
  "deskpro_url":"http://otherbrand.com",
  "apps_feedback":false,
  "apps_kb":false,
  "apps_news":true,
  "apps_downloads":false,
  "iface_portal":true,
  "iface_widget":false,
  "show_ratings":true,
  "show_ratings_min_votes":8,
  "publish_comments":true
}
    """
    Then the response should be in JSON
    And the response status code should be 204

  Scenario: I check updated brand settings
    When I send a GET request to "/api/v2/settings/brands/{lastCreatedId}/portal/general"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.deskpro_name" should be equal to "Other Brand"
    And the JSON node "data.deskpro_url" should be equal to "http://otherbrand.com"
    And the JSON node "data.apps_feedback" should be false
    And the JSON node "data.apps_kb" should be false
    And the JSON node "data.apps_news" should be true
    And the JSON node "data.apps_downloads" should be false
    And the JSON node "data.iface_portal" should be true
    And the JSON node "data.iface_widget" should be false
    And the JSON node "data.show_ratings" should be true
    And the JSON node "data.show_ratings_min_votes" should be equal to "8"
    And the JSON node "data.publish_comments" should be true


  Scenario: I check that the name and url are saved in the brand
    When I send a GET request to "/api/v2/brands/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.name" should be equal to "Other Brand"
    And the JSON node "data.url" should be equal to "otherbrand.com"

  Scenario: I check that the default brand settings remain the same
    When I send a GET request to "/api/v2/settings/brands/1/portal/general"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.deskpro_name" should be equal to "Test site"
    And the JSON node "data.deskpro_url" should be equal to "http://testsite.com"
    And the JSON node "data.apps_feedback" should be true
    And the JSON node "data.apps_kb" should be true
    And the JSON node "data.apps_news" should be true
    And the JSON node "data.apps_downloads" should be true
    And the JSON node "data.iface_portal" should be true
    And the JSON node "data.iface_widget" should be true
    And the JSON node "data.show_ratings" should be true
    And the JSON node "data.show_ratings_min_votes" should be equal to "6"
    And the JSON node "data.publish_comments" should be true