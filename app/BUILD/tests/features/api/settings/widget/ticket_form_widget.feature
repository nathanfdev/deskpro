Feature: Ticket Form Widget
  To embed a ticket form into any web site
  As a DeskPRO admin
  I want an API to generate embeddable code

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get the default code
    When I send a GET request to "/api/v2/ticket-form-widget/code"
    Then the response status code should be 200
    And the response should contain "<script type=\"text/javascript\">"

  Scenario: I get the code with custom settings
    When I send a GET request to "/api/v2/ticket-form-widget/code?language=swahili&department=54321&width=12345px"
    Then the response status code should be 200
    And the response should contain "<script type=\"text/javascript\">"
    And the response should contain "swahili"
    And the response should contain "54321"
    And the response should contain "12345px"
