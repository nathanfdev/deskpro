Feature: DbalAgentTeamCompiler

  Scenario: Compile the term
    And I have an IS AgentTeamTerm with the options:
      | Option         | Value       |
      | agent_team_ids | [1, 3, 199] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id IN (:x)
    """
    And the parameters should be:
      | Parameter | Value       |
      | x         | [1, 3, 199] |

  Scenario: Compile the not term
    And I have a NOT AgentTeamTerm with the options:
      | Option         | Value |
      | agent_team_ids | [18]  |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id NOT IN (:x)
    """
    And the parameters should be:
      | Parameter | Value |
      | x         | [18]  |
