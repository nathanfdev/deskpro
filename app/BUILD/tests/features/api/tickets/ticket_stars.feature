@new
Feature: /tickets endpoint
  I want to check person stars

  Background:
    Given no TicketFlagged records exist

  Scenario: I check ticket star prop as NULL
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
      | t3 | Ticket 3 |

    When I send a GET request to "/api/v2/tickets"
    Then the response status code should be 200
    And the JSON node "data[0].star" should be null
    And the JSON node "data[1].star" should be null
    And the JSON node "data[2].star" should be null

  Scenario Outline: I check own stars
    Given agent person exists
    And admin person exists
    And I'm authenticated as "<role>"
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
      | t3 | Ticket 3 |
    And only the following TicketFlagged records exist:
      | Person  | Ticket | color  |
      | {admin} | {t1}   | blue   |
      | {agent} | {t1}   | green  |

    When I send a GET request to "/api/v2/tickets/<ticket>"
    Then the JSON node "data.star" should be equal to the string "<color>"

    When I send a GET request to "/api/v2/tickets/{t3}"
    And the JSON node "data.star" should be null

    Examples:
      | role  | ticket | color  |
      | admin | {t1}   | blue   |
      | agent | {t1}   | green  |

  Scenario: I create a ticket with star
    Given I'm authenticated as admin
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Test Ticket",
  "star": "green"
}
    """
    Then the response status code should be 201
    And the JSON node "data.star" should be equal to the string "green"

  Scenario: I verify that ticket star assigned to the proper person
    Given only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
    And only the following TicketFlagged records exist:
      | Person  | Ticket | color |
      | {admin} | {t1}   | green |

    When I'm authenticated as agent
    And I send a PUT request to "/api/v2/tickets/{t1}" with body:
    """
{
  "star": "yellow"
}
    """
    Then the response status code should be 204

    When I'm authenticated as admin
    Then I send a GET request to "/api/v2/tickets/{t1}"
    And the response status code should be 200
    And the JSON node "data.star" should be equal to the string "green"

    When I'm authenticated as agent
    Then I send a GET request to "/api/v2/tickets/{t1}"
    And the response status code should be 200
    And the JSON node "data.star" should be equal to the string "yellow"

  Scenario: I unset star
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
    And only the following TicketFlagged records exist:
      | Person  | Ticket | color  |
      | {admin} | {t1}   | blue   |

    When I send a PUT request to "/api/v2/tickets/{t1}" with body:
    """
{
  "star": null
}
    """
    Then the response status code should be 204
    And I send a GET request to "/api/v2/tickets/{t1}"
    And the response status code should be 200
    And the JSON node "data.star" should be null
