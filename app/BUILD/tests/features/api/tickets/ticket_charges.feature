@new
Feature: Ticket charges

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And only the following Organization records exist:
      | #  | Name  |
      | o1 | Org 1 |
    And only the following Ticket records exist:
      | #  | Subject  | Person | Organization |
      | t1 | Ticket 1 | {user} | {o1}         |
      | t2 | Ticket 2 | {user} | NULL         |
    And only the following TicketCharge records exist:
      | #  | Ticket | Person | Agent   | Charge Time | Amount | Date Created |
      | c1 | {t1}   | {user} | {admin} | 5           | 1      | 2018-01-11 00:00:00   |
      | c2 | {t2}   | {user} | {admin} | 3           | 2      | 2018-01-11 00:00:00   |
      | c3 | {t1}   | {user} | {agent} | 5           | 4      | 2018-01-11 00:00:00   |

  Scenario: I retrieve a list of ticket charges
    When I send a GET request to "/api/v2/tickets/{t1}/charges?order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{c1}"
    And the JSON node "data[1].id" should be equal to "{c3}"

  Scenario: I get a ticket charge
    When I send a GET request to "/api/v2/tickets/{t1}/charges/{c1}"
    Then the JSON node "data.id" should be equal to "{c1}"
    And the JSON node "data.ticket" should be equal to "{t1}"
    And the JSON node "data.person" should be equal to "{user}"
    And the JSON node "data.agent" should be equal to "{admin}"
    And the JSON node "data.amount" should be equal to "1"
    And the JSON node "data.charge_time" should be equal to "5"
    And the JSON node "data.date_created" should be equal to "2018-01-11T00:00:00+0000"

  Scenario: I create a new ticket charge
    When I send a POST request to "/api/v2/tickets/{t1}/charges" with body:
    """
{
  "amount": 6.5,
  "charge_time": 100
}
    """
    Then the response status code should be 201
    And the JSON node "data.ticket" should be equal to "{t1}"
    And the JSON node "data.person" should be equal to "{user}"
    And the JSON node "data.agent" should be equal to "{admin}"
    And the JSON node "data.organization" should be equal to "{o1}"
    And the JSON node "data.amount" should be equal to "6.5"
    And the JSON node "data.charge_time" should be equal to "100"

  Scenario: I update a ticket charge
    When I send a PUT request to "/api/v2/tickets/{t1}/charges/{c1}" with body:
    """
{
  "amount": 6.5,
  "charge_time": 100
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/charges/{c1}"
    Then the JSON node "data.id" should be equal to "{c1}"
    And the JSON node "data.ticket" should be equal to "{t1}"
    And the JSON node "data.person" should be equal to "{user}"
    And the JSON node "data.agent" should be equal to "{admin}"
    And the JSON node "data.amount" should be equal to "6.5"
    And the JSON node "data.charge_time" should be equal to "100"
    And the JSON node "data.date_created" should be equal to "2018-01-11T00:00:00+0000"

  Scenario: I create comment of a ticket charge
    Given only the following CustomDefBilling records exist:
      | #  | Type | Title   |
      | f1 | text | Comment |
    When I send a POST request to "/api/v2/tickets/{t1}/charges" with body:
    """
{
  "amount": 1,
  "charge_time": 1,
  "comment": "my comment"
}
    """
    Then the response status code should be 201
    And the JSON node "data.comment" should be equal to "my comment"

  Scenario: I update comment of a ticket charge
    Given only the following CustomDefBilling records exist:
      | #  | Type | Title   |
      | f1 | text | Comment |
    And the object "c1" has "f1" custom data set to "my comment"

    When I send a PUT request to "/api/v2/tickets/{t1}/charges/{c1}" with body:
    """
{
  "amount": 1,
  "charge_time": 1,
  "comment": "my updated comment"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/charges/{c1}"
    Then the JSON node "data.comment" should be equal to "my updated comment"

  Scenario: I try to delete a charge of another ticket
    When I send a DELETE request to "/api/v2/tickets/{t2}/charges/{c1}"
    Then the response status code should be 404

  Scenario: I delete ticket charge
    When I send a DELETE request to "/api/v2/tickets/{t1}/charges/{c1}"
    Then the response status code should be 200
