Feature: DbalDepartmentCompiler

  Scenario: Compile the term
    And I have an IS DepartmentTerm with the options:
      | Option         | Value      |
      | department_ids | [1, 2, 15] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.department_id IN (:y)
    """
    And the parameters should be:
      | Parameter | Value      |
      | y         | [1, 2, 15] |

  Scenario: Compile the not term
    And I have a NOT DepartmentTerm with the options:
      | Option         | Value       |
      | department_ids | [2, 97, 10] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.department_id NOT IN (:n)
    """
    And the parameters should be:
      | Parameter | Value       |
      | n         | [2, 97, 10] |
