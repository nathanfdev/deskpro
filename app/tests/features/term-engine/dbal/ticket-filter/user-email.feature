Feature: DBAL Ticket Filter UserEmailTerm compiler
  In order to search for a ticket by user email
  As a developer
  I need to use UserEmail terms

  Scenario: Compile the term
    And I have an IS UserEmailTerm with the options:
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
    LEFT JOIN people_emails ON (ticket.person_id = people_emails.person_id)
    """
    And the parameters should be:
      | Parameter | Value                     |
      | x         | chris.tickner@deskpro.com |

  Scenario: Compile the not term
    And I have a NOT UserEmailTerm with the options:
      | Option | Value                     |
      | email  | chris.tickner@deskpro.com |
    When I compile my terms
    Then I should have a DbalCompiledResult
    And the unique join string should be like:
    """
    LEFT JOIN people_emails people_emails_0 ON (ticket.person_id = people_emails_0.person_id AND people_emails_0.email = :x)
    """
    And the WHERE clause should be like:
    """
    people_emails_0.id IS NULL
    """
    And the parameters should be:
      | Parameter | Value                     |
      | x         | chris.tickner@deskpro.com |
