@basic
Feature: Api endpoints providing sideloading features

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I'm loading tickets list with sideloading
    When I send a GET request to "/api/v2/tickets?include=person"
    Then the response should be in JSON
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 3 elements

  Scenario: I'm loading tickets list with sideloading (several sideloading keys)
    When I send a GET request to "/api/v2/tickets?include=person,usergroup"
    Then the response should be in JSON
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 3 elements
    And the JSON node "linked.usergroup" should exist
    And the JSON node "linked.usergroup" should have 7 elements

  Scenario: I'm loading tickets list with wrong sideloading key
    When I send a GET request to "/api/v2/tickets?include=person,foobar"
    Then the response should be in JSON
    And the JSON node "linked" should exist
    And the JSON node "linked.person" should exist
    And the JSON node "linked.person" should have 3 elements
    And the JSON node "linked.foobar" should not exist

