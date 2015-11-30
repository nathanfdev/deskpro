@counts @publish-nav
Feature: Content counts endpoints (/articles/counts, /news/counts, /downloads/counts)
  To retrieve counts of various DeskPRO content
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario Outline: I select counts grouping them by author not applying any filters
    When I send a GET request to "/api/v2/<endpoint>/counts?group_by=author"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 8
    And the JSON node "data.grouped_by" should be equal to "author"

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |

  Scenario Outline: I select counts grouping them by period_updated and filtering by status, author, period_updated and category
    When I send a GET request to "/api/v2/<endpoint>/counts?group_by=period_updated&status=published&author=1&period_created=ever&category=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2
    And the JSON node "data.grouped_by" should be equal to "period_updated"

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |

  Scenario Outline: I select counts grouping them by categories
    When I send a GET request to "/api/v2/<endpoint>/counts?group_by=category"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 8
    And the JSON node "data.grouped_by" should be equal to "category"

    # check counts reflect categories hierarchy
    And the JSON node "data.nested[0].nested[0].nested[0].nested[0].id" should be equal to "9"
    And the JSON node "data.nested[0].nested[0].nested[0].nested[0].count" should be equal to "1"

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |

  Scenario Outline: I select total count of drafts
    When I send a GET request to "/api/v2/<endpoint>/counts?status=hidden&hidden_status=draft"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 1
    And the JSON node "data.nested" should have 0 elements

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |