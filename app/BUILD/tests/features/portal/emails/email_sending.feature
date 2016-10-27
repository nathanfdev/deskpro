Feature: Email should contain basic content

  Background:
    Given I have a ticket

  Scenario: Content is present in tickets
    When A ticket message is sent
    Then I should receive an email containing test

