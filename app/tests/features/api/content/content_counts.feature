@counts @chat-nav
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
    And the JSON node "data.nested.grouped_by" should be equal to "author"

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
    And the JSON node "data.nested.grouped_by" should be equal to "period_updated"

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |