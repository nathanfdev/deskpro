Feature: /outgoing-emails endpoint
  To process outgoing emails
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "agent"
    And there are no "SendmailSource" records

  Scenario: I get email
    Given only the following SendmailSource records exist:
      | #  | Uuid                                 | Status     |
      | s1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | processing |
    When I send a GET request to "/api/v2/outgoing-emails/1dd5cc48-ec64-440b-90ad-f814284445ab"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "processing"

  Scenario: I abort email
    Given only the following SendmailSource records exist:
      | #  | Uuid                                 | Status     |
      | s1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | processing |
    When I send a POST request to "/api/v2/outgoing-emails/1dd5cc48-ec64-440b-90ad-f814284445ab/abort"
    Then the response status code should be 204
    When I send a GET request to "/api/v2/outgoing-emails/1dd5cc48-ec64-440b-90ad-f814284445ab"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "aborted"

  Scenario: I retry email
    Given only the following SendmailSource records exist:
      | #  | Uuid                                 | Status     |
      | s1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | processing |
    When I send a POST request to "/api/v2/outgoing-emails/1dd5cc48-ec64-440b-90ad-f814284445ab/retry"
    Then the response status code should be 204
    When I send a GET request to "/api/v2/outgoing-emails/1dd5cc48-ec64-440b-90ad-f814284445ab"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "retry"

  Scenario: I send email
    Given I've just created a new email account "dev@deskprodev.com"
    And only the following SendmailSource records exist:
      | #  | Uuid                                 | Status   | EmailAccount                       |
      | s1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | inserted | {email_account_dev@deskprodev.com} |
    When I send a POST request to "/api/v2/outgoing-emails/1dd5cc48-ec64-440b-90ad-f814284445ab/send"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "error"

  Scenario: I send emails in batch
    Given I've just created a new email account "dev@deskprodev.com"
    And only the following SendmailSource records exist:
      | #  | Uuid                                 | Status   | EmailAccount                       |
      | s1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | inserted | {email_account_dev@deskprodev.com} |
      | s1 | 1dd5cc48-ec64-440b-90ad-f814284445ac | inserted | {email_account_dev@deskprodev.com} |
    When I send a POST request to "/api/v2/outgoing-emails/batch/send" with body:
    """
{
  "uuids": ["1dd5cc48-ec64-440b-90ad-f814284445ab", "1dd5cc48-ec64-440b-90ad-f814284445ac"]
}
    """
    Then the response status code should be 200
    And the JSON node "1dd5cc48-ec64-440b-90ad-f814284445ab" should be equal to "error"
    And the JSON node "1dd5cc48-ec64-440b-90ad-f814284445ac" should be equal to "error"
