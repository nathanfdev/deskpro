Feature: UTF-8 response
  
  Scenario: I access homepage and verify UTF-8 charser
    When I send a GET request to "/"
    Then the response should be encoded in "utf-8"


