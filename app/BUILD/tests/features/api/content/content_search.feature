Feature: /articles, /news, /downloads endpoints
  To retrieve various sets of DeskPRO Articles/News/Downloads
  As an API user
  I want API endpoints with search capabilities

  Background:
    Given I install the api data set
    And my request is authenticated
    And I set permission "articles.use" = 1 for "registered" usergroup
    And I set permission "downloads.use" = 1 for "registered" usergroup
    And I set permission "news.use" = 1 for "registered" usergroup

  Scenario: Reinstall hack scenario to not put it on the next Scenario Outline
    Given I send a GET request to "/api/v2/languages"

  Scenario Outline: I search for content with empty search criteria
    When I send a GET request to "/api/v2/<endpoint>"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination.count" should be equal to 8
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 8
    And the JSON node "data[0].title" should be equal to "<first_title>"

    Examples:
      | endpoint  | first_title       |
      | articles  | Test Article #8   |
      | news      | Test News #8      |
      | downloads | Test Download #8  |

  Scenario Outline: I search for content filtering by status, hidden_status, author, category and period_created
    When I send a GET request to "/api/v2/<endpoint>?status=hidden&hidden_status=draft&author=1&category=1&period_created=ever"
    Then the response should be in JSON
    And the response status code should be 200

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |

  Scenario: I GET list of articles from category id=1
    When I send a GET request to "/api/v2/articles?sort=date_created&order=desc&category=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6
    And the JSON node "meta.pagination.per_page" should be equal to 10
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I GET list of news from category id=1
    When I send a GET request to "/api/v2/news?sort=date_created&order=desc&category=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6
    And the JSON node "meta.pagination.per_page" should be equal to 10
    And the JSON node "meta.pagination.total_pages" should be equal to 1

  Scenario: I GET list of downloads from category id=1
    When I send a GET request to "/api/v2/downloads?sort=date_created&order=desc&category=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 6
    And the JSON node "meta.pagination.per_page" should be equal to 10
    And the JSON node "meta.pagination.total_pages" should be equal to 1
