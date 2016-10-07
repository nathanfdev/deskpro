@new
Feature: Content counts endpoints (/articles/counts, /news/counts, /downloads/counts)
  To retrieve counts of various DeskPRO content
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And only the following "Article" records exist:
      | #  | slug | title | content  | person | status    | hidden_status |
      | a1 | art1 | Art1  | Article1 | {me}   | published |               |
      | a2 | art2 | Art2  | Article2 |        | hidden    | draft         |
      | a3 | art3 | Art3  | Article3 | {me}   | published |               |
    And only the following "News" records exist:
      | #  | slug  | title | content | person | status    | hidden_status |
      | n1 | news1 | News1 | News1   | {me}   | published |               |
      | n2 | news2 | News2 | News2   |        | hidden    | draft         |
      | n3 | news3 | News3 | News3   | {me}   | published |               |
    And only the following "Download" records exist:
      | #  | slug      | title     | content   | person | status    | hidden_status |
      | d1 | download1 | Download1 | Download1 | {me}   | published |               |
      | d2 | download2 | Download2 | Download2 |        | hidden    | draft         |
      | d3 | download3 | Download3 | Download3 | {me}   | published |               |


  Scenario Outline: I select counts grouping them by author not applying any filters
    When I send a GET request to "/api/v2/<endpoint>/counts?group_by=author"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to "author"

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |

  Scenario Outline: I select counts grouping them by period_updated and filtering by status, author
    When I send a GET request to "/api/v2/<endpoint>/counts?group_by=period_updated&status=published&author={me}"
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
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to "category"

    # check counts reflect categories hierarchy
    # todo aha, everything is pretty clear: nested[1].nested[1].nested[1].nested[1] should be 10, bingo?
    # And the JSON node "data.nested[0].nested[0].nested[0].nested[0].id" should be equal to "9"
    # And the JSON node "data.nested[0].nested[0].nested[0].nested[0].count" should be equal to "1"

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
