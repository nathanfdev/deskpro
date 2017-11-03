@new
Feature: Custom fields
  I want to create a custom field

  Background:
    Given I'm authenticated as admin

## TEXT FIELD

  Scenario Outline: I create a ticket custom text field type without an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Text"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                            | Title               |
      | /api/v2/ticket_custom_fields        | Text field          |
      | /api/v2/person_custom_fields        | Text field          |
      | /api/v2/organization_custom_fields  | Text field          |


  Scenario Outline: I create a ticket custom text field type with an alias
    Given I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "alias": "<Alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Text"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                            | Title               | Alias          |
      | /api/v2/ticket_custom_fields        | Text field          | james_vagabond |
      | /api/v2/person_custom_fields        | Text field          | james_vagabond |
      | /api/v2/organization_custom_fields  | Text field          | james_vagabond |


## TEXTAREA FIELD

  Scenario Outline: I create a ticket custom textarea field type without an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Textarea"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                            | Title               |
      | /api/v2/ticket_custom_fields        | Textarea field      |
      | /api/v2/person_custom_fields        | Textarea field      |
      | /api/v2/organization_custom_fields  | Textarea field      |

  Scenario Outline: I create a ticket custom textarea field type with an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "alias": "<Alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Textarea"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                            | Title               | Alias          |
      | /api/v2/ticket_custom_fields        | Textarea field      | james_vagabond |
      | /api/v2/person_custom_fields        | Textarea field      | james_vagabond |
      | /api/v2/organization_custom_fields  | Textarea field      | james_vagabond |


## DATE FIELD

  Scenario Outline: I create a ticket custom date field type without an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Date"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                            | Title               |
      | /api/v2/ticket_custom_fields        | Date field          |
      | /api/v2/person_custom_fields        | Date field          |
      | /api/v2/organization_custom_fields  | Date field          |

  Scenario Outline: I create a ticket custom date field type with an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "alias": "<Alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Date"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                            | Title               | Alias          |
      | /api/v2/ticket_custom_fields        | Date field          | james_vagabond |
      | /api/v2/person_custom_fields        | Date field          | james_vagabond |
      | /api/v2/organization_custom_fields  | Date field          | james_vagabond |

## DATETIME FIELD

  Scenario Outline: I create a ticket custom date time field type without an alias
    When I send a POST request to "<Endpoint>" with body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DateTime"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                            | Title               |
      | /api/v2/ticket_custom_fields        | Datetime field      |
      | /api/v2/person_custom_fields        | Datetime field      |
      | /api/v2/organization_custom_fields  | Datetime field      |

  Scenario Outline: I create a ticket custom date time field type with an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "alias": "<Alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DateTime"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                            | Title               | Alias          |
      | /api/v2/ticket_custom_fields        | Datetime field      | james_vagabond |
      | /api/v2/person_custom_fields        | Datetime field      | james_vagabond |
      | /api/v2/organization_custom_fields  | Datetime field      | james_vagabond |

## DATA LIST FIELD

  Scenario Outline: I create a ticket custom data list field type without an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DataList"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                             | Title          |
      | /api/v2/ticket_custom_fields        | Data list       |
      | /api/v2/person_custom_fields        | Data list       |
      | /api/v2/organization_custom_fields  | Data list       |

  Scenario Outline: I create a ticket custom data list field type with an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "alias": "<Alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DataList"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                             | Title          | Alias          |
      | /api/v2/ticket_custom_fields        | Data list       | james_vagabond |
      | /api/v2/person_custom_fields        | Data list       | james_vagabond |
      | /api/v2/organization_custom_fields  | Data list       | james_vagabond |


## DATA JSON FIELD

  Scenario Outline: I create a ticket custom data json field type without an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DataJson"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                             | Title     |
      | /api/v2/ticket_custom_fields        | Data json  |
      | /api/v2/person_custom_fields        | Data json  |
      | /api/v2/organization_custom_fields  | Data json  |

  Scenario Outline: I create a ticket custom data json field type with an alias
    When I send a POST request to "<Endpoint>" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "alias": "<Alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DataJson"
}
    """

    Then the response status code should be 201

    Examples:
      | Endpoint                             | Title     | Alias          |
      | /api/v2/ticket_custom_fields        | Data json  | james_vagabond |
      | /api/v2/person_custom_fields        | Data json  | james_vagabond |
      | /api/v2/organization_custom_fields  | Data json  | james_vagabond |

## PREDEFINED CHOICE FIELDs

  Scenario Outline: I create a predefined choice ticket custom field of without an alias
    When I send a POST request to "/api/v2/ticket_custom_fields" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Choice",
  "field_type":"<Field Type>",
  "choices_structure": [
    { "id":"cb_1", "@is_new":true, "title":"first choice", "parent_id":null, "display_order":10, "depth":0 },
    { "id":"cb_2", "@is_new":true, "title":"second choice", "parent_id":null, "display_order":20, "depth":0 },
    { "id":"cb_3", "@is_new":true, "title":"third choice", "parent_id":null, "display_order":20, "depth":0 }
  ],
  "default_value":null,
  "validation_type":"required",
  "min_length":1
}

    """

    Then the response status code should be 201
    Then print the corresponding curl command

    Examples:
      | Field Type    | Title                   |
      | multi_select  | Multiple Choice field   |
      | select        | select                  |
      | radio         | radio                   |
      | checkbox      | checkbox                |


  Scenario Outline: I create a predefined choice ticket custom field of with an alias
    When I send a POST request to "/api/v2/ticket_custom_fields" with a json body:
    """
{
  "title":"<Title>",
  "is_enabled":true,
  "alias": "<Alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Choice",
  "field_type":"<Field Type>",
  "choices_structure": [
    { "id":"cb_1", "@is_new":true, "title":"first choice", "parent_id":null, "display_order":10, "depth":0 },
    { "id":"cb_2", "@is_new":true, "title":"second choice", "parent_id":null, "display_order":20, "depth":0 },
    { "id":"cb_3", "@is_new":true, "title":"third choice", "parent_id":null, "display_order":20, "depth":0 }
  ],
  "default_value":null,
  "validation_type":"required",
  "min_length":1
}

    """

    Then the response status code should be 201
    Then print the corresponding curl command

    Examples:
      | Field Type    | Title                   | Alias          |
      | multi_select  | Multiple Choice field   | james_vagabond |
      | select        | select                  | james_vagabond |
      | radio         | radio                   | james_vagabond |
      | checkbox      | checkbox                | james_vagabond |

