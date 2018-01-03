@new
Feature: /organizations endpoint
  To check contact data validation
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"

  Scenario: I check contact data validation
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "New Organization",
  "contact_data": {
    "website": [{}, {"url": ""}, {"url": "/sitecom"}, {"url": "ftp://site.com"}],
    "facebook": [{}, {"url": ""}, {"url": "/facebook.com"}, {"url": "http://facebook.com"}],
    "linked_in": [{}, {"url": ""}, {"url": "/linkedin.com"}, {"url": "http://linkedin.com"}],
    "instant_message": [{"username": "", "service": "unknown_service"}],
    "twitter": [{}],
    "address": [{}],
    "phone": [{}, {"type": "mobile", "code": 1, "number": 12345}]
  }
}
    """
    Then the response should be in JSON
    And the response status code should be 400

    And the JSON node "errors.fields.contact_data.fields.website.fields.website_0.fields.url.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.website.fields.website_1.fields.url.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.website.fields.website_2.fields.url.errors[0].code" should be equal to "invalid_url"
    And the JSON node "errors.fields.contact_data.fields.website.fields.website_3.fields.url.errors[0].code" should be equal to "invalid_url"

    And the JSON node "errors.fields.contact_data.fields.facebook.fields.facebook_0.fields.url.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.facebook.fields.facebook_1.fields.url.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.facebook.fields.facebook_2.fields.url.errors[0].code" should be equal to "invalid_url"
    And the JSON node "errors.fields.contact_data.fields.facebook.fields.facebook_2.fields.url.errors[1].code" should be equal to "facebook_url"
    And the JSON node "errors.fields.contact_data.fields.facebook.fields.facebook_3.fields.url.errors[0].code" should be equal to "facebook_url"

    And the JSON node "errors.fields.contact_data.fields.linked_in.fields.linked_in_0.fields.url.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.linked_in.fields.linked_in_1.fields.url.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.linked_in.fields.linked_in_2.fields.url.errors[0].code" should be equal to "invalid_url"
    And the JSON node "errors.fields.contact_data.fields.linked_in.fields.linked_in_2.fields.url.errors[1].code" should be equal to "linked_in_url"
    And the JSON node "errors.fields.contact_data.fields.linked_in.fields.linked_in_3.fields.url.errors[0].code" should be equal to "linked_in_url"

    And the JSON node "errors.fields.contact_data.fields.instant_message.fields.instant_message_0.fields.username.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.instant_message.fields.instant_message_0.fields.service.errors[0].code" should be equal to "bad_choice"

    And the JSON node "errors.fields.contact_data.fields.twitter.fields.twitter_0.fields.username.errors[0].code" should be equal to "required"

    And the JSON node "errors.fields.contact_data.fields.address.fields.address_0.fields.address.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.address.fields.address_0.fields.city.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.address.fields.address_0.fields.country.errors[0].code" should be equal to "required"

    And the JSON node "errors.fields.contact_data.fields.phone.fields.phone_0.fields.code.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.phone.fields.phone_0.fields.number.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.phone.fields.phone_0.fields.type.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.contact_data.fields.phone.fields.phone_1.fields.number.errors[0].code" should be equal to "invalid_phone_number_format"
