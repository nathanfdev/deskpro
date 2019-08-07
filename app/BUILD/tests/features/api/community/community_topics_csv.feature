@new
Feature:
  To export filtered list of community topics to CSV file
  As an API user
  I need /community_topics/csv endpoint

  Background:
    Given I'm authenticated as agent
    And I have permissions to use community
    And I have the following "CommunityChannel" records:
      | #  | Title                    |
      | c1 | First community channel  |
      | c2 | Second community channel |
    And I have the following "CommunityTopicStatusCategory" records:
      | #   | StatusType | Title                                   |
      | sc1 | active     | First active community status category  |
      | sc2 | active     | Second active community status category |
      | sc3 | closed     | First closed community status category  |
    And I have the following "CommunityTopic" records:
      | #   | Person  | Channel | StatusCategory | Title        | Status | IsReviewed | Content |
      | ct1 | {agent} | {c1}    | {sc1}          | First topic  | active | 1          |         |
      | ct2 | {agent} | {c1}    | {sc2}          | Second topic | active | 1          |         |
      | ct2 | {agent} | {c2}    | {sc2}          | Second topic | closed | 1          |         |

  Scenario: I GET list of community topics in CSV format
    When I send a GET request to "/api/v2/community_topics/csv?category=First+community+channel&status_category={sc1}&count=200"
    Then the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "First topic"
    And the JSON node "meta" should exist
