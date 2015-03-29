Feature: DbalTicketStatusCompiler

  Scenario: Compile the term using no hidden statuses
    And I have an IS TicketStatusTerm with the options:
      | Option | Value                      |
      | status | [awaiting_agent, resolved] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.status IN (:x)
    """
    And the parameters should be:
      | Parameter | Value                      |
      | x         | [awaiting_agent, resolved] |

  Scenario: Compile the term using a HIDDEN status
    And I have an IS TicketStatusTerm with the options:
      | Option | Value           |
      | status | [spam, deleted] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.status = :y AND ticket.hidden_status IN (:z)
    """
    And the parameters should be:
      | Parameter | Value           |
      | y         | hidden          |
      | z         | [spam, deleted] |

  Scenario: Compile the term BOTH non hidden and HIDDEN status
    And I have an IS TicketStatusTerm with the options:
      | Option | Value                            |
      | status | [awaiting_agent, resolved, spam] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.status IN (:x) OR (ticket.status = :y AND ticket.hidden_status IN (:z))
    """
    And the parameters should be:
      | Parameter | Value                      |
      | x         | [awaiting_agent, resolved] |
      | y         | hidden                     |
      | z         | [spam]                     |

  Scenario: Compile the not term
    And I have a NOT TicketStatusTerm with the options:
      | Option | Value                |
      | status | [archived, resolved] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.status NOT IN (:x)
    """
    And the parameters should be:
      | Parameter | Value                |
      | x         | [archived, resolved] |

  Scenario: Compile the not term WITH HIDDEN
    And I have a NOT TicketStatusTerm with the options:
      | Option | Value                                 |
      | status | [archived, validating, deleted, spam] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.status NOT IN (:x) OR (ticket.status = :y AND ticket.hidden_status NOT IN (:z))
    """
    And the parameters should be:
      | Parameter | Value                       |
      | x         | [archived]                  |
      | y         | hidden                      |
      | z         | [validating, deleted, spam] |

  Scenario: Compile the not term only a HIDDEN
    And I have a NOT TicketStatusTerm with the options:
      | Option | Value                       |
      | status | [validating, deleted, spam] |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    ticket.status != :y OR (ticket.status = :y AND ticket.hidden_status NOT IN (:z))
    """
    And the parameters should be:
      | Parameter | Value                       |
      | y         | hidden                      |
      | z         | [validating, deleted, spam] |
