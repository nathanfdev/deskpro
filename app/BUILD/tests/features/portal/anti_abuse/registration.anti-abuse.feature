Feature: To prevent registration spam
  Anti-abuse system should work

  Background:
    Given I install the "fresh" data set
    Given rate limit table is empty
    And the following languages are enabled:
      | default |

  Scenario: Checking lockout response
    Given I set "registration" rate limit to 1 attempt within 1 minute with "lockout" response and 15 minutes lockout time
    And I am on "/register"
    And I get captcha code from the form field "captcha"
    When I fill in the following:
      | person_registration[primary_email][email] | Luke0BBY@tatooine.galaxy |
      | person_registration[name]                 | Luke Skywalker           |
      | person_registration[password][password]   | iamthejediknight         |
      | person_registration[password][confirm]    | iamthejediknight         |
      | person_registration[timezone]             | Africa/Tunis             |
    And I fill in "person_registration[captcha][captcha]" with "{captchaCode}"
    And I press "Register"

    When I am on "/register"
    Then I should see "You have registered too many times." in the ".inline-form-alert" element

  Scenario: Checking captcha response
    Given I set "registration" rate limit to 1 attempt within 1 minute with "captcha" response
    And I am on "/register"
    When I fill in the following:
      | person_registration[primary_email][email] | Leia0BBY@alderaan.galaxy |
      | person_registration[name]                 | Leia Organa Solo         |
      | person_registration[password][password]   | iamtheprincess           |
      | person_registration[password][confirm]    | iamtheprincess           |
      | person_registration[timezone]             | Africa/Tunis             |
    And I press "Register"

    When I am on "/register"
    And I should see an "#person_registration_captcha_captcha" element
