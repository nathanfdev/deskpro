@new
Feature: Brand Settings Setup

  Background:
    Given I'm authenticated as admin
    And no Brand records exist
    And I have only default brand

  Scenario: I get default brand settings
    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/portal/general"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I update default brand settings
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/portal/general" with body:
    """
{
  "brand_name": "Test site (same)",
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
    And the response status code should be 200

    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/portal/general"
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
  "brand_name": "My new Brand",
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
    Then the response status code should be 200

    When I send a GET request to "/api/v2/settings/brands/{lastCreatedId}/portal/general"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.brand_name" should be equal to "My new Brand"
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

    When I send a GET request to "/api/v2/brands/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.name" should be equal to "My new Brand"
    And the JSON node "data.url" should be equal to "otherbrand.com"

    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/portal/general"
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