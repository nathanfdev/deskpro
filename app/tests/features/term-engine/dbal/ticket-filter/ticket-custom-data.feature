Feature: DbalTicketCustomDataCompiler

  Scenario: Term with values instead of input
    And I have an IS TicketCustomDataTerm with the options:
      | Option   | Value   |
      | field_id | 1       |
      | values   | [3, 11] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    custom_data_ticket_0.value IN (:x)
    """
    And the unique join string should be like:
    """
    LEFT JOIN custom_data_ticket custom_data_ticket_0 ON (custom_data_ticket_0.ticket_id = ticket.id AND custom_data_ticket_0.field_id = :y)
    """
    And the parameters should be:
      | Parameter | Value   |
      | x         | [3, 11] |
      | y         | 1       |

  Scenario: Term with input instead of values
    And I have an IS TicketCustomDataTerm with the options:
      | Option   | Value    |
      | field_id | 1        |
      | input    | my input |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    custom_data_ticket_0.input = :x
    """
    And the unique join string should be like:
    """
    LEFT JOIN custom_data_ticket custom_data_ticket_0 ON (custom_data_ticket_0.ticket_id = ticket.id AND custom_data_ticket_0.field_id = :y)
    """
    And the parameters should be:
      | Parameter | Value    |
      | x         | my input |
      | y         | 1        |
