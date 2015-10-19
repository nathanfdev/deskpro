Feature: /avatars/* endpoints
  To retrieve avatars of different DeskPRO entities
  As a developer
  I want API endpoints to retrieve avatar URLs

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario Outline: I get avatars list
    When I send a GET request to "/api/v2/avatars/<target>?ids=1,2"
    And the response status code should be 200
    And the JSON node "meta.count" should be equal to "2"
    And the JSON node "data[0].id" should exist
    And the JSON node "data[0].url" should exist
    And the JSON node "data[0].is_fallback" should exist
    And the JSON node "data[0].url_pattern" should exist
    And the JSON node "data[0].url_pattern" should contain "{{IMG_SIZE}}"

    Examples:
      | target        |
      | person        |
      | organization  |
      | agent_team    |
      | department    |

  Scenario: I get gravatar URL of a Person
    When I send a GET request to "/api/v2/avatars/person?ids=1,2"
    And the response status code should be 200
    And the JSON node "meta.count" should be equal to "2"
    And the JSON node "data[0].gravatar" should exist

