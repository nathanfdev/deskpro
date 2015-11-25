@feedback-nav
Feature: /feedback/ endpoint
  To obtain filtered list of feedback
  As a developer
  I want an endpoint for feedback select

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: I GET list of feedback with hidden_status set to validating
    When I send a GET request to "/api/v2/feedback/?awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 37

  Scenario: I GET list of feedback with hidden_status set to validating and side-loaded author info
    When I send a GET request to "/api/v2/feedback/?include=person&awaiting_validation=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 1 element
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 37

  Scenario: I GET list of feedback with hidden_status set to validating and pagination set to 2 results per page
    When I send a GET request to "/api/v2/feedback/?awaiting_validation=1&count=2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 37
    And the JSON node "meta.pagination.count" should be equal to 2
    And the JSON node "meta.pagination.per_page" should be equal to 2
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 19

  Scenario: I GET list of feedback with active status category
    When I send a GET request to "/api/v2/feedback/?status=active&status_category=Gathering+Feedback"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 9

  Scenario: I GET list of active feedback
    When I send a GET request to "/api/v2/feedback/?status=active"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 18

  Scenario: I GET list of feedback from one category
    When I send a GET request to "/api/v2/feedback/?category=Suggestion"
    Then the response should be in JSON
#    And print last JSON response
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 37

  Scenario: I GET list of feedback tagged with one label
    When I send a GET request to "/api/v2/feedback/?custom_category=Linux"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.count" should be equal to 2

  Scenario: I GET feedback without any label
    When I send a GET request to "/api/v2/feedback/?no_labels=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 61

  Scenario: I GET feedback created after 2015-08-01
    When I send a GET request to "/api/v2/feedback/?created_from=2015-08-01"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 61

  Scenario: I GET feedback created after 2015-08-01 but before 2015-10-01
    When I send a GET request to "/api/v2/feedback/?created_from=2015-08-01&created_to=2015-10-01"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination" should exist
    And the JSON node "meta.pagination.total" should be equal to 21
