Feature: /avatars/* endpoints
  To retrieve avatars of different DeskPRO entities
  As a developer
  I want API endpoints to retrieve avatar URLs

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario Outline: I get avatar information
    When I send a GET request to "/api/v2/avatars/<target>/1"
    And the response status code should be 200
    And the JSON node "url" should exist
    And the JSON node "url_pattern" should exist
    And the JSON node "url_pattern" should contain "{{IMG_SIZE}}"

    Examples:
      | target        |
      | person        |
      | organization  |

  Scenario: I get gravatar URL of a Person
    When I send a GET request to "/api/v2/avatars/person/1"
    And the response status code should be 200
    And the JSON node "gravatar" should exist
