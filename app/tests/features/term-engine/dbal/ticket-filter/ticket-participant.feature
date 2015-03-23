Feature: DbalTicketParticipantCompiler

  Scenario: Compile the term
    And I have an IS TicketParticipantTerm with the options:
      | Option     | Value  |
      | person_ids | [4, 9] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    tickets_participants_0.person_id IN (:x)
    """
    And the unique join string should be like:
    """
    LEFT JOIN tickets_participants tickets_participants_0 ON (tickets_participants_0.ticket_id = ticket.id)
    """
    And the parameters should be:
      | Parameter | Value  |
      | x         | [4, 9] |

  Scenario: Compile the not term
    And I have a NOT TicketParticipantTerm with the options:
      | Option     | Value |
      | person_ids | [11]  |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    tickets_participants_0.person_id NOT IN (:x)
    """
    And the unique join string should be like:
    """
    LEFT JOIN tickets_participants tickets_participants_0 ON (tickets_participants_0.ticket_id = ticket.id)
    """
    And the parameters should be:
      | Parameter | Value |
      | x         | [11]  |
