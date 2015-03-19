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
    And the WHERE clause should be like:
    """
    {from}.agent_id IN (:x)
    """
    And the parameters should be:
      | Parameter | Value    |
      | x         | 1, 2, 15 |

  Scenario: Compile the not term
    And I have a NOT AgentTerm with the options:
      | Option    | Value   |
      | agent_ids | 2,97,10 |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    {from}.agent_id NOT IN (:x)
    """
    And the parameters should be:
      | Parameter | Value     |
      | x         | 2, 97, 10 |
