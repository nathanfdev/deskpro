@new
Feature: /guides_comments endpoint
  To reply guide comment
  As an API user
  I want an endpoint to reply an guide comment

  Background:
    Given I'm authenticated as admin
    And no "Guides" records exist
    And only the following "GuideCategory" records exist:
      | #    | title                         |
      | csc1 | First Guide Category          |


  Scenario: I reply an guide comment
    Given only the following "Guides" records exist:
      | #        | slug      | title                | status    |
      | art1   | Test1       | Test Comment1        | published |
    And only the following "GuideComment" records exist:
      | #    | topic        | person  | content             | is_reviewed  |
      | com1 | {art1}       | {admin} | comment1           | 1            |
    When I send a POST request to "/api/v2/guides_comments" with parameters:
      | key             | value                         |
      | content        | reply to guide comment        |
      | status         | visible                       |
      | topic          | ~art1~                       |
      | parent_id     | ~com1~                        |
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.parent" should exist
    And the JSON node "data.parent" should be equal to "{com1}"


  Scenario: I reply an guide comment with status "deleted"
    Given only the following "Guides" records exist:
      | #        | slug      | title               | status    |
      | art1   | Test1      | Test Comment1        | published |
    And only the following "GuideComment" records exist:
      | #    | topic         | person  | content      | is_reviewed     | status   |
      | com1 | {art1}        | {admin} | comment1      | 1           |  deleted |
    When I send a POST request to "/api/v2/guides_comments" with parameters:
      | key             | value                     |
      | content        | reply to guide comment      |
      | status         | visible                    |
      | topic        | ~art1~                      |
      | parent_id    | ~com1~                      |
    Then the response should be in JSON
    And the response status code should be 400



  Scenario: I reply an guide comment with wrong parent_id and download combination
    Given only the following "Guides" records exist:
      | #        | slug      | title                | status    |
      | art1    | Test1      | Test Comment1        | published |
      | art2   | Test2      | Test Comment2        | published  |
    And only the following "GuideComment" records exist:
      | #    | topic        | person        | content      | is_reviewed | status    |
      | com1 | {art1}       | {admin}      | comment1      | 1           |  visible  |
      | com2 | {art2}        | {admin}      | comment2     | 1           |  visible  |
    When I send a POST request to "/api/v2/guides_comments" with parameters:
      | key             | value                     |
      | content         | reply to guide comment  |
      | status          | visible                    |
      | topic          | ~art1~                     |
      | parent_id     | ~com2~                      |
    Then the response should be in JSON
    And the response status code should be 400
