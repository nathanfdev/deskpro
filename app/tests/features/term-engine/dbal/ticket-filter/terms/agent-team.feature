Feature: DbalAgentTeamCompiler

  Scenario: Compile the term with ids
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

  Scenario: Compile the term with "me"
    And I have an IS AgentTeamTerm with the options:
      | Option         | Value           |
      | agent_team_ids | [1, 3, 199, me] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id IN (:x) OR ticket.agent_team_id IN (:y)
    """
    And the parameters should be:
      | Parameter | Value                                      |
      | x         | [1, 3, 199]                                |
      | y         | TermEngineExpression('agent_teams(agent)') |

  Scenario: Compile the term with NOT "me"
    And I have a NOT AgentTeamTerm with the options:
      | Option         | Value           |
      | agent_team_ids | [1, 3, 199, me] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id NOT IN (:x) AND ticket.agent_team_id NOT IN (:y)
    """
    And the parameters should be:
      | Parameter | Value                                      |
      | x         | [1, 3, 199]                                |
      | y         | TermEngineExpression('agent_teams(agent)') |

  Scenario: Compile the term with "unassigned"
    And I have an IS AgentTeamTerm with the options:
      | Option         | Value                   |
      | agent_team_ids | [1, 3, 199, unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id IN (:x) OR ticket.agent_team_id IS NULL
    """
    And the parameters should be:
      | Parameter | Value       |
      | x         | [1, 3, 199] |

  Scenario: Compile the term with just "unassigned"
    And I have an IS AgentTeamTerm with the options:
      | Option         | Value        |
      | agent_team_ids | [unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id IS NULL
    """
    And there should be no parameters

  Scenario: Compile the term with NOT "unassigned"
    And I have a NOT AgentTeamTerm with the options:
      | Option         | Value        |
      | agent_team_ids | [unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id IS NOT NULL
    """
    And there should be no parameters

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


  Scenario: Compile the term with several
    And I have an IS AgentTeamTerm with the options:
      | Option         | Value                       |
      | agent_team_ids | [1, 3, 199, me, unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id IN (:x) OR ticket.agent_team_id IN (:y) OR ticket.agent_team_id IS NULL
    """
    And the parameters should be:
      | Parameter | Value                                      |
      | x         | [1, 3, 199]                                |
      | y         | TermEngineExpression('agent_teams(agent)') |


  Scenario: Compile the term with several NOT
    And I have a NOT AgentTeamTerm with the options:
      | Option         | Value                       |
      | agent_team_ids | [1, 3, 199, me, unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_team_id NOT IN (:x) AND ticket.agent_team_id NOT IN (:y) AND ticket.agent_team_id IS NOT NULL
    """
    And the parameters should be:
      | Parameter | Value                                      |
      | x         | [1, 3, 199]                                |
      | y         | TermEngineExpression('agent_teams(agent)') |
