@new
Feature: Form scalar transformer
  To prevent array to string conversions on scalar fields

  Background:
    Given I have only default brand
    And the following languages are enabled:
      | default |
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And default everyone user group exists

  Scenario: I check ticket form
    Given only the following Department records exist:
      | #  | Parent | Title        | Brands           | Is Tickets Enabled |
      | d1 | NULL   | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | NULL   | Department 2 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone
    And I grant the "{d2}" department permission of tickets app for usergroup everyone
    When I send a POST request to "/new-ticket" with parameters:
      | key                        | value |
      | ticket[department][]       | Value |
      | ticket[subject][]          | Value |
      | ticket[message][message][] | Value |
      | ticket[message][format][]  | Value |
    Then the response status code should not be 500

  Scenario: I check feedback form
    When I send a POST request to "/feedback" with parameters:
      | key                     | value |
      | new_feedback[content][] | Value |
      | new_feedback[title][]   | Value |
    Then the response status code should not be 500

  Scenario: I check register form
    When I send a POST request to "/register" with parameters:
      | key                         | value |
      | person_registration[name][] | Value |
    Then the response status code should not be 500

  Scenario: I check reset password form
    When I send a POST request to "/login/reset-password" with parameters:
      | key                             | value |
      | password_reset_request[email][] | Value |
    Then the response status code should not be 500

  Scenario: I check profile form
    Given I'm authenticated as user
    When I send a POST request to "/profile" with parameters:
      | key                    | value |
      | person_profile[name][] | Value |
    Then the response status code should not be 500
