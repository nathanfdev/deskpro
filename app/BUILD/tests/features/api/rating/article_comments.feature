@new
Feature: /article_comments endpoint
  To rate articles comments
  As an API user
  I want an endpoint to rate articles comments

  Background:
    Given I'm authenticated as admin
    And no "Article" records exist
    And only the following "NewsCategory" records exist:
      | #    | title                     |
      | csc1 | First News Category       |

  Scenario: I GET rate count of articles comments
    Given only the following "Article" records exist:
      | #        | slug      | title          | status    |
      | down1   | Download1 | Test Comment1   | published |
    And only the following "ArticleComment" records exist:
      | #    | article    | person  | content      | is_reviewed |
      | com1 | {down1}    | {admin} | comment1     | 1            |
    When I send a GET request to "/api/v2/article_comments/{com1}/rate_count"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[1].total" should exist


  Scenario: I upvote a articles comment
    Given only the following "Article" records exist:
      | #        | slug      | title          | status    |
      | down1   | Download1 | Test Comment1   | published |
    And only the following "ArticleComment" records exist:
      | #    | article  | person  | content      | is_reviewed |
      | com1 | {down1}  | {admin} | comment1 | 1               |
    When I send a POST request to "/api/v2/article_comments/{com1}/rate" with body:
    """
{
  "upvote": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.total_rating" should exist

    And the JSON node "data.total_rating" should be equal to "1"


  Scenario: I downvote a articles comment
    Given only the following "Article" records exist:
      | #        | slug      | title          | status     |
      | down1   | Aticle1    | Test Comment1   | published |
    And only the following "ArticleComment" records exist:
      | #    | article   | person  | content      | is_reviewed |
      | com1 | {down1}  | {admin} | comment1 | 1          |
    When I send a POST request to "/api/v2/article_comments/{com1}/rate" with body:
    """
{
  "upvote": 0
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.total_rating" should exist

    And the JSON node "data.total_rating" should be equal to "-1"
