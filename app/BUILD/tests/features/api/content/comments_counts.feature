@counts @publish-nav @basic
Feature: Comment counts endpoints (/article_comments/counts, /news_comments/counts, /download_comments/counts)
  To retrieve counts of DeskPRO comments
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I select comment counts
    When I send a GET request to "/api/v2/article_comments/counts"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario Outline: I select comment counts grouping them by status
    When I send a GET request to "/api/v2/<target>_comments/counts?group_by=status"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to "status"
    And the JSON node "data.nested" should have 2 elements

    Examples:
      | target    |
      | article   |
      | news      |
      | download  |

  Scenario Outline: I select comment counts grouping them by parent
    When I send a GET request to "/api/v2/<target>_comments/counts?group_by=<target>"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to "<target>"
    And the JSON node "data.nested" should have 2 element

    Examples:
      | target    |
      | article   |
      | news      |
      | download  |

  Scenario Outline: I select comment counts grouping them by parent and filtering by parent, status, is_reviewed and period_created
    When I send a GET request to "/api/v2/<target>_comments/counts?group_by=<target>&<target>=2&status=visible&is_reviewed=0&period_created=ever"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 1
    And the JSON node "data.grouped_by" should be equal to "<target>"
    And the JSON node "data.nested" should have 1 elements

    Examples:
      | target    |
      | article   |
      | news      |
      | download  |
