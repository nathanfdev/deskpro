@new
Feature:
  To export filtered list of feedback to CSV file
  As an API user
  I need /feedback/csv endpoint

  Background:
    Given I'm authenticated as agent
    And I have permissions to use feedback
    And I have the following FeedbackCategory records:
      | #  | Title                    |
      | c1 | First feedback category  |
      | c2 | Second feedback category |
    And I have the following FeedbackStatusCategory records:
      | #   | StatusType | Title                                  |
      | sc1 | active     | First active feedback status category  |
      | sc2 | active     | Second active feedback status category |
      | sc3 | closed     | First closed feedback status category  |
    And I have the following Feedback records:
      | #  | Person              | Category | StatusCategory | Title           | Status | IsReviewed |
      | f1 | {agent@deskpro.dev} | {c1}     | {sc1}          | First feedback  | active | 1          |
      | f2 | {agent@deskpro.dev} | {c1}     | {sc2}          | Second feedback | active | 1          |
      | f2 | {agent@deskpro.dev} | {c2}     | {sc2}          | Second feedback | closed | 1          |

  Scenario: I GET list of feedback in CSV format
    When I send a GET request to "/api/v2/feedback/csv?category=First+feedback+category&status_category={sc1}&count=200"
    Then the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "First feedback"
    And the JSON node "meta" should exist
