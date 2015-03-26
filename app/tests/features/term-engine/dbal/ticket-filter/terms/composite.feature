Feature: DbalCompositeCompiler

  Scenario: Compile the term
    Given I enter an OR composite term
    And I have an IS DepartmentTerm with the options:
      | Option         | Value  |
      | department_ids | [4, 5] |
    And I have a NOT AgentTerm with the options:
      | Option    | Value       |
      | agent_ids | [9, 17, 80] |
    And I close the composite term
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    (ticket.department_id IN (:d) OR ticket.agent_id NOT IN (:a))
    """
    And the parameters should be:
      | Parameter | Value       |
      | d         | [4, 5]      |
      | a         | [9, 17, 80] |
