Feature: DBAL Ticket Filter TicketStatus compiler
  In order to search the Ticket data
  As a developer
  I need to use ticket status terms

  Scenario: Compile the term
    And I have an IS TicketStatusTerm with the options:
      | Option | Value                    |
      | status | awaiting_agent, resolved |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.status IN (:x)
    """
    And the parameters should be:
      | Parameter | Value                    |
      | x         | awaiting_agent, resolved |

  Scenario: Compile the not term
    And I have a NOT TicketStatusTerm with the options:
      | Option | Value                                                    |
      | status | archived, hidden.validating, hidden.deleted, hidden.spam |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.status NOT IN (:x)
    """
    And the parameters should be:
      | Parameter | Value                                                    |
      | x         | archived, hidden.validating, hidden.deleted, hidden.spam |
