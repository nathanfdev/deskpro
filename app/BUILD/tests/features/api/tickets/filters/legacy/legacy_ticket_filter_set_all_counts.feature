@ticket-filters
Feature: /ticket_filter_sets/all/counts endpoint
  To ticket filters grouping count
  As a developer
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of ticket filter sets counts
