Feature: DBAL Term Compiler
  In order to search the Ticket data with specific terms
  As a developer
  I want to compile terms into a usable query builder

  Scenario: Compile an agent term
    And I have an AgentTerm with the options:
      | Option    | Value    |
      | agent_ids | 1, 2, 15 |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the SQL should be like:
    """
    SELECT * FROM tickets ticket WHERE ticket.agent_id IN (:agents)
    """
    And the parameters should be:
      | Parameter | Value    |
      | agents    | 1, 2, 15 |
