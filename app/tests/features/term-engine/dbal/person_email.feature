Feature: DBAL Agent term compiler
  In order to search the Ticket data
  As a developer
  I need to use agent terms

  Scenario: Compile the term
    And I have an IS PersonEmailTerm with the options:
      | Option | Value                     |
      | email  | chris.tickner@deskpro.com |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    people_emails.email = :x
    """
    And the join string should be like:
    """
    LEFT JOIN people_emails ON {from}.person_id = people_emails.person_id
    """
    And the parameters should be:
      | Parameter | Value                     |
      | x         | chris.tickner@deskpro.com |

  Scenario: Compile the not term
    And I have a NOT PersonEmailTerm with the options:
      | Option | Value                     |
      | email  | chris.tickner@deskpro.com |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the WHERE clause should be like:
    """
    NOT EXISTS ( SELECT 1 FROM people_emails pe WHERE pe.email = :x AND pe.person_id = {from}.person_id )
    """
    And the parameters should be:
      | Parameter | Value                     |
      | x         | chris.tickner@deskpro.com |
