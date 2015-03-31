Feature: DbalAgentCompiler

  Scenario: Compile the term with "me"
    Given I have an IS AgentTerm with the options:
      | Option    | Value          |
      | agent_ids | [1, 2, 15, me] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_id IN (:x)
    """
    And the parameters should be:
      | Parameter | Value                                             |
      | x         | [1, 2, 15, TermEngineExpression('agent.getId()')] |

  Scenario: Compile the term with NOT "me"
    And I have a NOT AgentTerm with the options:
      | Option    | Value          |
      | agent_ids | [1, 2, 15, me] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_id NOT IN (:x)
    """
    And the parameters should be:
      | Parameter | Value                                             |
      | x         | [1, 2, 15, TermEngineExpression('agent.getId()')] |

  Scenario: Compile the term with "unassigned"
    And I have an IS AgentTerm with the options:
      | Option    | Value                  |
      | agent_ids | [1, 2, 15, unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_id IN (:x) OR ticket.agent_id IS NULL
    """
    And the parameters should be:
      | Parameter | Value      |
      | x         | [1, 2, 15] |

  Scenario: Compile the term with "unassigned"
    And I have an IS AgentTerm with the options:
      | Option    | Value        |
      | agent_ids | [unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_id IS NULL
    """
    And there should be no parameters

  Scenario: Compile the term with NOT "unassigned"
    And I have a NOT AgentTerm with the options:
      | Option    | Value        |
      | agent_ids | [unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_id IS NOT NULL
    """
    And there should be no parameters

  Scenario: Compile the not term
    And I have a NOT AgentTerm with the options:
      | Option    | Value       |
      | agent_ids | [2, 97, 10] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_id NOT IN (:x)
    """
    And the parameters should be:
      | Parameter | Value       |
      | x         | [2, 97, 10] |

  Scenario: Compile the term with several
    And I have an IS AgentTerm with the options:
      | Option    | Value                      |
      | agent_ids | [1, 2, 15, me, unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_id IN (:x) OR ticket.agent_id IS NULL
    """
    And the parameters should be:
      | Parameter | Value                                             |
      | x         | [1, 2, 15, TermEngineExpression('agent.getId()')] |

  Scenario: Compile the term with several NOT
    And I have a NOT AgentTerm with the options:
      | Option    | Value                      |
      | agent_ids | [1, 2, 15, me, unassigned] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.agent_id NOT IN (:x) AND ticket.agent_id IS NOT NULL
    """
    And the parameters should be:
      | Parameter | Value                                             |
      | x         | [1, 2, 15, TermEngineExpression('agent.getId()')] |
