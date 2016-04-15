Feature: Comment counts endpoints (/article_comments, /news_comments, /download_comments)
  To retrieve DeskPRO comments
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And I set permission "articles.use" = 1 for "registered" usergroup
    And I set permission "downloads.use" = 1 for "registered" usergroup
    And I set permission "news.use" = 1 for "registered" usergroup

  Scenario Outline: I retrieve list of comments
    When I send a GET request to "/api/v2/<target>_comments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "meta.pagination.total" should be equal to 3

    Examples:
      | target    |
      | article   |
      | news      |
      | download  |

  Scenario Outline: I select comments filtering them by parent, status, is_reviewed and period_created
    When I send a GET request to "/api/v2/<target>_comments?<target>=2&status=visible&is_reviewed=0&period_created=ever"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "meta.pagination.total" should be equal to 1

    Examples:
      | target    |
      | article   |
      | news      |
      | download  |
