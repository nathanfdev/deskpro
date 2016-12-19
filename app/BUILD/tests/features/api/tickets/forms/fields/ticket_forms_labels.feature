@new
Feature: /ticket_forms
  I want to check label fields

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I add labels
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "labels": ["label 1", "label 3"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "label 1"
    And the JSON node "data.labels[1]" should be equal to "label 3"

  Scenario: I change labels
    Given only the following LabelTicket records exist:
      | Ticket | Label   |
      | {t1}   | label 1 |
      | {t1}   | label 2 |
      | {t1}   | label 3 |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "labels": ["label 2", "label 3"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "label 2"
    And the JSON node "data.labels[1]" should be equal to "label 3"

  Scenario: I clear labels
    Given only the following LabelTicket records exist:
      | Ticket | Label   |
      | {t1}   | label 1 |
      | {t1}   | label 2 |
      | {t1}   | label 3 |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "labels": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.labels" should have 0 elements
