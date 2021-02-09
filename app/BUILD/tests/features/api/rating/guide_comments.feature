@new
Feature: /guides_comments endpoint
  To rate guide comments
  As an API user
  I want an endpoint to guide comments

  Background:
    Given I'm authenticated as admin
    And no "Guides" records exist
    And only the following "GuideCategory" records exist:
      | #    | title                     |
      | csc1 | First News Category       |

  Scenario: I GET rate count of guide comments
    Given only the following "Guides" records exist:
      | #        | slug         | title           | status    | guide     |
      | down1   |  Guides1      | Test Comment1   | published | {csc1}   |
    And only the following "GuideComment" records exist:
      | #    | topic   | person  | content        | is_reviewed |
      | com1 | {down1} | {admin} | comment1      | 1            |
    When I send a GET request to "/api/v2/guides_comments/{com1}/rate_count"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[1].total" should exist


  Scenario: I upvote a guide comment
    Given only the following "Guides" records exist:
      | #        | slug         | title           | status    | guide     |
      | down1   |  Guides1      | Test Comment1   | published | {csc1}   |
    And only the following "GuideComment" records exist:
      | #    | topic   | person  | content        | is_reviewed |
      | com1 | {down1} | {admin} | comment1      | 1            |
    When I send a POST request to "/api/v2/guides_comments/{com1}/rate" with body:
    """
{
  "upvote": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.total_rating" should exist

    And the JSON node "data.total_rating" should be equal to "1"


  Scenario: I downvote a guide comment
    Given only the following "Guides" records exist:
      | #        | slug         | title           | status    | guide    |
      | down1   |  Guides1      | Test Comment1   | published | {csc1}   |
    And only the following "GuideComment" records exist:
      | #    | topic   | person  | content        | is_reviewed |
      | com1 | {down1} | {admin} | comment1      | 1            |
    When I send a POST request to "/api/v2/guides_comments/{com1}/rate" with body:
    """
{
  "upvote": 0
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.total_rating" should exist

    And the JSON node "data.total_rating" should be equal to "-1"
