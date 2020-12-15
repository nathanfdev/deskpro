@new
Feature: /api/v2/articles/{id}/splash_image_upload
  To upload splash images to article

  Background:
    Given I'm authenticated as admin
    And only the following Article records exist:
      | #  | Title        | Slug           | Content              | person          |
      | t1 | Test Article | test-article   | Test Content        | {admin}         |
    And I attach image file to my request

  Scenario: I upload a splash image
    When I send a POST request to "/api/v2/articles/{t1}/splash_image_upload" with "DummyImage"
    Then the response status code should be 200
    And the JSON node "data.image" should exist

  Scenario: I try to select a splash image for article
    When I send a POST request to "/api/v2/articles/{t1}/splash_image" with body:
    """
{
    "image": {
        "id": "GMgdkY1xfDA",
        "width": 4512,
        "height": 3000,
        "color": "#E8F5FB",
        "blur_hash": "LGDS,d00R%RO-:IURioIt6%gMwxu",
        "description": "Peaceful park in Porto in autumn",
        "alt_description": "people walking on gray concrete road between green trees during daytime",
        "urls": {
            "raw": "https://example.net/photo-1585555799372-523effb3a9ba?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjkxMDYyfQ",
            "full": "https://example.net/photo-1585555799372-523effb3a9ba?ixlib=rb-1.2.1&q=85&fm=jpg&crop=entropy&cs=srgb&ixid=eyJhcHBfaWQiOjkxMDYyfQ",
            "regular": "https://example.net/photo-1585555799372-523effb3a9ba?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=1080&fit=max&ixid=eyJhcHBfaWQiOjkxMDYyfQ",
            "small": "https://example.net/photo-1585555799372-523effb3a9ba?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=400&fit=max&ixid=eyJhcHBfaWQiOjkxMDYyfQ",
            "thumb": "https://example.net/photo-1585555799372-523effb3a9ba?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&ixid=eyJhcHBfaWQiOjkxMDYyfQ"
        },
        "links": {
            "self": "https://example.net/photos/GMgdkY1xfDA",
            "html": "https://xx.urlcom/photos/GMgdkY1xfDA",
            "download": "https:/example.net/photos/GMgdkY1xfDA/download",
            "download_location": "https://example.net/photos/GMgdkY1xfDA/download"
        }
    }
}
    """
    Then the response status code should be 200
    And the JSON node "data.id" should exist

