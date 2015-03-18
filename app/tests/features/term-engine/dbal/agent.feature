Feature: DBAL Agent term compiler
  In order to search the Ticket data
  As a developer
  I need to use agent terms

  Scenario: Compile the term
    And I have an IS AgentTerm with the options:
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

  Scenario: Compile the not term
    And I have a NOT AgentTerm with the options:
      | Option    | Value   |
      | agent_ids | 2,97,10 |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the SQL should be like:
    """
    SELECT * FROM tickets ticket WHERE ticket.agent_id NOT IN (:agents)
    """
    And the parameters should be:
      | Parameter | Value     |
      | agents    | 2, 97, 10 |
