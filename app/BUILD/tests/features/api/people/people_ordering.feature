@new
Feature: /people endpoint
  Check list ordering

  Background:
    Given there are no "Person" records
    And I'm authenticated as agent

  Scenario Outline: I order by 'date_created' and 'date_last_login'
    Given the following User records exist:
      | #  | Name   | <person_field>      |
      | u1 | User 1 | 2017-03-22 00:00:00 |
      | u2 | User 2 | 2017-03-20 00:00:00 |
      | u3 | User 3 | 2017-03-21 00:00:00 |

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=<order_field>&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u2}"
    And the JSON node "data[1].id" should be equal to "{u3}"
    And the JSON node "data[2].id" should be equal to "{u1}"

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=<order_field>&order_dir=desc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u3}"
    And the JSON node "data[2].id" should be equal to "{u2}"

    Examples:
      | person_field    | order_field     |
      | Date Created    | date_created    |
      | Date Last Login | date_last_login |

  Scenario: I order by 'id'
    Given the following User records exist:
      | #  | Name   |
      | u1 | User 1 |
      | u2 | User 2 |
      | u3 | User 3 |

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=id&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u2}"
    And the JSON node "data[2].id" should be equal to "{u3}"

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=id&order_dir=desc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u3}"
    And the JSON node "data[1].id" should be equal to "{u2}"
    And the JSON node "data[2].id" should be equal to "{u1}"

  Scenario Outline: I order by 'name', 'first_name' and 'last_name'
    Given the following User records exist:
      | #  | <person_field> |
      | u1 | User 2         |
      | u2 | User 1         |
      | u3 | User 3         |

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=<order_field>&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u2}"
    And the JSON node "data[1].id" should be equal to "{u1}"
    And the JSON node "data[2].id" should be equal to "{u3}"

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=<order_field>&order_dir=desc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u3}"
    And the JSON node "data[1].id" should be equal to "{u1}"
    And the JSON node "data[2].id" should be equal to "{u2}"

    Examples:
      | person_field | order_field |
      | Name         | name        |
      | First Name   | first_name  |
      | Last Name    | last_name   |

  Scenario: I order by 'primary_email'
    Given the following User records exist:
      | #  | Name   | Email             |
      | u1 | User 1 | user1@example.com |
      | u2 | User 2 | user2@example.com |
      | u3 | User 3 | user3@example.com |

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=primary_email&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u2}"
    And the JSON node "data[2].id" should be equal to "{u3}"

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=primary_email&order_dir=desc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u3}"
    And the JSON node "data[1].id" should be equal to "{u2}"
    And the JSON node "data[2].id" should be equal to "{u1}"

  Scenario: I order by 'timezone'
    Given the following User records exist:
      | #  | Name   | Timezone |
      | u1 | User 1 | Europe/London |
      | u2 | User 2 | US/Mountain   |
      | u3 | User 3 | Europe/Moscow |

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=timezone&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u3}"
    And the JSON node "data[2].id" should be equal to "{u2}"

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=timezone&order_dir=desc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u2}"
    And the JSON node "data[1].id" should be equal to "{u3}"
    And the JSON node "data[2].id" should be equal to "{u1}"

  Scenario: I order by 'organization'
    Given only the following Organization records exist:
      | #  | Name  |
      | o1 | Org 1 |
      | o2 | Org 2 |
      | o3 | Org 3 |
    And the following User records exist:
      | #  | Name   | Organization |
      | u1 | User 1 | {o3}         |
      | u2 | User 2 | {o1}         |
      | u3 | User 3 | {o2}         |

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=organization&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u2}"
    And the JSON node "data[1].id" should be equal to "{u3}"
    And the JSON node "data[2].id" should be equal to "{u1}"

    When I send a GET request to "/api/v2/people?is_agent=0&order_by=organization&order_dir=desc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{u1}"
    And the JSON node "data[1].id" should be equal to "{u3}"
    And the JSON node "data[2].id" should be equal to "{u2}"
