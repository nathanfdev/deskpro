@new
Feature: I check ticket layouts versioning

  Background:
    Given I'm authenticated as agent

  Scenario: I check 20170401 version
    When I send a GET request to "/api/v2/20170401/ticket_layouts/agent"
    Then the JSON node "[0].fields" should exist

    When I send a GET request to "/api/v2/20170401/ticket_layouts/agent/default"
    Then the JSON node "fields" should exist
