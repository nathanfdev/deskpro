@new
Feature: /incoming-emails endpoint
  To process incoming emails
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "agent"
    And there are no "EmailSource" records

  Scenario: I get email
    Given only the following EmailSource records exist:
      | #   | Uuid                                 | Status     |
      | es1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | processing |
    When I send a GET request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "processing"

  Scenario: I abort email
    Given only the following EmailSource records exist:
      | #   | Uuid                                 | Status     |
      | es1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | processing |
    When I send a POST request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab/abort"
    Then the response status code should be 204
    When I send a GET request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "rejected"

  Scenario: I retry email
    Given only the following EmailSource records exist:
      | #   | Uuid                                 | Status     |
      | es1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | processing |
    When I send a POST request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab/retry"
    Then the response status code should be 204
    When I send a GET request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab"
    Then the response status code should be 200
    And the JSON node "data.status" should be equal to "retry"

  Scenario: I create email with existing uuid
    Given only the following EmailSource records exist:
      | #   | Uuid                                 | Status     |
      | es1 | 1dd5cc48-ec64-440b-90ad-f814284445ab | processing |
    And I add "Content-Type" header equal to "message/rfc822"
    When I send a POST request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab"
    Then the response status code should be 409

  Scenario: I create email
    When I send a POST request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab" with content type "message/rfc822" and file "/resources/emails/incoming-email.eml" as body
    Then the response status code should be 201
    And the JSON node "data.status" should be equal to "inserted"
    And the JSON node "data.uuid" should be equal to "1dd5cc48-ec64-440b-90ad-f814284445ab"

  Scenario: I execute email and check log
   Given there are no "Ticket" records
   And I've just created a new email account "dev@deskprodev.com"
   And I send a POST request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab" with content type "message/rfc822" and file "/resources/emails/incoming-email.eml" as body

   When I send a POST request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab/execute"
   And I skip Email gateway EmailSource runner shutdown logic
   Then the response status code should be 200
   And the JSON node "data.status" should be equal to "complete"
   And the JSON node "data.from_email" should be equal to "testuser@deskpro.com"

   When I send a GET request to "/api/v2/incoming-emails/1dd5cc48-ec64-440b-90ad-f814284445ab/log"
   Then the response status code should be 200