Feature: DBAL Composite term compiler
  In order to perform compound searches
  As a developer
  I need a composite term

  Scenario: Compile the term
    Given I enter an OR composite term
    And I have an IS DepartmentTerm with the options:
      | Option         | Value |
      | department_ids | 4,5   |
    And I have a NOT AgentTerm with the options:
      | Option    | Value   |
      | agent_ids | 9,17,80 |
    And I close the composite term
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the SQL should be like:
    """
    SELECT * FROM tickets ticket WHERE (ticket.department_id IN (:departments) OR ticket.agent_id NOT IN (:agents))
    """
    And the parameters should be:
      | Parameter   | Value   |
      | departments | 4,5     |
      | agents      | 9,17,80 |
