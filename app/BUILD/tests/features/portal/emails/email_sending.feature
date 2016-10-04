Feature: Email should contain basic content

  Background:
    Given I have a ticket

  Scenario: I access API controller throwing an HTTP exception
    When A ticket message is sent
    Then I should receive an email containing test

