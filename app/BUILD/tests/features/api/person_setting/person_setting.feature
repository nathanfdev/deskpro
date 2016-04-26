@person-setting
Feature: /person_setting endpoint
  To store and obtain person settings
  As a developer
  I want an endpoint for person settings

  Background:
    Given I install the "api" data set
    And my request is authenticated

  Scenario: I GET person setting for feedback view fields
    When I send a GET request to "/api/v2/person_setting/feedback_display_fields"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.name" should exist
    And the JSON node "data.name" should be equal to "feedback_display_fields"
    And the JSON node "data.value" should exist
    And the JSON node "data.value.card" should exist
    And the JSON node "data.value.table" should exist