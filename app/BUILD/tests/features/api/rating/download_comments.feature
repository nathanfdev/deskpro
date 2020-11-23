@new
Feature: /download_comments endpoint
  To rate download comments
  As an API user
  I want an endpoint to rate download comments

  Background:
    Given I'm authenticated as admin
    And no "Download" records exist
    And only the following "DownloadCategory" records exist:
      | #    | title                     |
      | csc1 | First Downloads Category  |

  Scenario: I GET rate count of download comments
    Given only the following "Download" records exist:
      | #        | slug      | title          | status    | category |
      | down1   | Download1 | Test Download1 | published | {csc1}   |
    And only the following "DownloadComment" records exist:
      | #    | download | person  | content  | is_reviewed |
      | com1 | {down1} | {admin} | comment1 | 1            |
    When I send a GET request to "/api/v2/download_comments/{com1}/rate_count"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[1].total" should exist


  Scenario: I upvote a community topic comment
    Given only the following "Download" records exist:
      | #        | slug      | title          | status    | category |
      | down1   | Download1 | Test Download1 | published | {csc1}   |
    And only the following "DownloadComment" records exist:
      | #    | download | person  | content  | is_reviewed |
      | com1 | {down1}  | {admin} | comment1 | 1          |
    When I send a POST request to "/api/v2/download_comments/{com1}/rate" with body:
    """
{
  "upvote": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.total_rating" should exist

    And the JSON node "data.total_total" be equal to "1"


  Scenario: I downvote a community topic comment
    Given only the following "Download" records exist:
      | #        | slug      | title          | status    | category |
      | down1   | Download1 | Test Download1 | published | {csc1}   |
    And only the following "DownloadComment" records exist:
      | #    | download | person  | content  | is_reviewed |
      | com1 | {down1}  | {admin} | comment1 | 1          |
    When I send a POST request to "/api/v2/download_comments/{com1}/rate" with body:
    """
{
  "upvote": 0
}
    """
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.total_rating" should exist

    And the JSON node "data.total_rating" should be equal to "-1"
