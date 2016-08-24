@new
Feature: Organizations list filters based on parent_id

  Background:
    Given I'm authenticated as admin

  Scenario: I filter by parent id
    Given only the following Organization records exist:
      | #   | Name   | Parent |
      | o1  | Org 1  | NULL   |
      | o2  | Org 2  | NULL   |
      | o1a | Org 1a | {o1}   |
      | o1b | Org 1b | {o1}   |
      | o2a | Org 2a | {o2}   |
      | o3  | Org 3  | NULL   |

    When I send a GET request to "/api/v2/organizations?parent={o1}"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{o1a}"
    And the JSON node "data[1].id" should be equal to "{o1b}"

    When I send a GET request to "/api/v2/organizations?parent={o2}"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{o2a}"

    When I send a GET request to "/api/v2/organizations?parent[]={o1}&parent[]={o2}"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{o1a}"
    And the JSON node "data[1].id" should be equal to "{o1b}"
    And the JSON node "data[2].id" should be equal to "{o2a}"

  Scenario: I filter by is_child
    Given only the following Organization records exist:
      | #   | Name   | Parent |
      | o1  | Org 1  | NULL   |
      | o2  | Org 2  | NULL   |
      | o1a | Org 1a | {o1}   |
      | o2a | Org 2a | {o2}   |

    When I send a GET request to "/api/v2/organizations"
    Then the JSON node "data" should have 4 elements

    When I send a GET request to "/api/v2/organizations?is_child=0"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{o1}"
    And the JSON node "data[1].id" should be equal to "{o2}"

    When I send a GET request to "/api/v2/organizations?is_child=1"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{o1a}"
    And the JSON node "data[1].id" should be equal to "{o2a}"

  Scenario: I order by parent_id
    Given only the following Organization records exist:
      | #   | Name   | Parent |
      | o1  | Org 1  | NULL   |
      | o2  | Org 2  | NULL   |
      | o1a | Org 1a | {o1}   |
      | o2a | Org 2a | {o2}   |
      | o1b | Org 1b | {o1}   |

    When I send a GET request to "/api/v2/organizations?&order_by=parent&order_dir=asc"
    Then the JSON node "data" should have 5 elements
    And the JSON node "data[0].id" should be equal to "{o1}"
    And the JSON node "data[1].id" should be equal to "{o2}"
    And the JSON node "data[2].id" should be equal to "{o1a}"
    And the JSON node "data[3].id" should be equal to "{o1b}"
    And the JSON node "data[4].id" should be equal to "{o2a}"
