Feature: /articles, /news, /downloads endpoints
  To retrieve various sets of DeskPRO Articles/News/Downloads
  As a developer
  I want API endpoints with search capabilities

  Background:
    Given I install the api data set
    And my request is authenticated

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
      | articles  | A test article    |
      | news      | Test News #1      |
      | downloads | Test Download #1  |

  Scenario Outline: I search for content filtering by status, hidden_status, author, category and period_created
    When I send a GET request to "/api/v2/<endpoint>?status=hidden&hidden_status=draft&author=1&category=1&period_created=ever"
    Then the response should be in JSON
    And the response status code should be 200

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |

  Scenario Outline: I search for content using not existing color filter
    When I send a GET request to "/api/v2/<endpoint>?color=red"
    Then the response should be in JSON
    And the response status code should be 400

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