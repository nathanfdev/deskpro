@feedback-nav @feedback
Feature: /feedback endpoint
  To obtain filtered list of feedback
  As a developer
  I want an endpoint for feedback select

  Background:
    Given I install the "api" data set
    And my request is authenticated
    And I set permission "feedback.use" = 1 for "registered" usergroup

  @reinstall
  Scenario: I GET list of feedback with hidden_status set to validating
    When I send a GET request to "/api/v2/feedback?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 18

  @basic
  Scenario: I GET list of feedback with hidden_status set to validating and side-loaded author info
    When I send a GET request to "/api/v2/feedback?include=person&awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 1 element
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 18

  Scenario: I GET list of feedback with hidden_status set to validating and pagination set to 2 results per page
    When I send a GET request to "/api/v2/feedback?awaiting_validation=1&count=2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 18
    And the JSON node "meta.pagination.count" should be equal to 2
    And the JSON node "meta.pagination.per_page" should be equal to 2
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 9

  Scenario: I GET list of feedback with active status category
    When I send a GET request to "/api/v2/feedback?status=active&status_category=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 8

  Scenario: I GET list of active feedback
    When I send a GET request to "/api/v2/feedback?status=active"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 32

  Scenario: I GET list of feedback from one category
    When I send a GET request to "/api/v2/feedback?category=Suggestion"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 37

  Scenario: I GET list of feedback tagged with one label
    When I send a GET request to "/api/v2/feedback?custom_category=Linux"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.count" should be equal to 2

  Scenario: I GET feedback without any label
    When I send a GET request to "/api/v2/feedback?no_labels=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 61

  Scenario: I GET feedback with any label
    When I send a GET request to "/api/v2/feedback?label[]=label1&label[]=label2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].labels" should have 1 element
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[1].labels" should have 2 elements
    And the JSON node "data[1].labels[0]" should be equal to "label1"
    And the JSON node "data[1].labels[1]" should be equal to "label2"

  Scenario: I GET feedback with all labels
    When I send a GET request to "/api/v2/feedback?label[]=label1&label[]=label2&labels_mode=all"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element

    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label2"

  Scenario: I GET feedback created after 2015-08-01
    When I send a GET request to "/api/v2/feedback?created_from=2015-08-01"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 61

  Scenario: I GET feedback created after 2015-08-01 but before 2015-10-01
    When I send a GET request to "/api/v2/feedback?created_from=2015-08-01&created_to=2015-10-01"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 21

  Scenario: I check feedback comments count
    When I send a GET request to "/api/v2/feedback?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].comments_count" should be equal to 2
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].comments_count" should be equal to 1
    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].comments_count" should be equal to 1
    And the JSON node "data[3].id" should be equal to 4
    And the JSON node "data[3].comments_count" should be equal to 0

  Scenario Outline: I order list
    When I send a GET request to "/api/v2/feedback?order_by=<order_by>&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data[0].<order_by>" should be equal to <min1>
    And the JSON node "data[1].<order_by>" should be equal to <min2>

    When I send a GET request to "/api/v2/feedback?order_by=<order_by>&order_dir=desc"
    Then the response status code should be 200
    And the JSON node "data[0].<order_by>" should be equal to <max1>
    And the JSON node "data[1].<order_by>" should be equal to <max2>

    Examples:
      | order_by     | min1                       | min2                       | max1                       | max2                       |
      | date_created | "2015-04-13T11:33:33+0000" | "2015-04-13T11:33:33+0000" | "2015-11-08T00:00:00+0000" | "2015-11-08T00:00:00+0000" |
      | id           | 1                          | 2                          | 64                         | 63                         |
      | total_rating | 0                          | 0                          | 5                          | 5                          |
      | num_ratings  | 0                          | 0                          | 6                          | 6                          |
      | title        | "Test feedback 1"          | "Test feedback 10"         | "Test feedback 9"          | "Test feedback 8"          |
      | status       | active                     | active                     | hidden                     | hidden                     |
      | category     | 1                          | 1                          | 3                          | 3                          |
      | person       | 1                          | 1                          | 1                          | 1                          |
