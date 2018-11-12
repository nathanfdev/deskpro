@new
Feature: /ticket_layouts endpoint
  I want to check required fields

  Background:
    Given I'm authenticated as admin
    And I have only default brand

  Scenario Outline: I check base props
    Given the only default ticket layout exists with fields:
      | <context>_layout |
      | department       |

    When I send a GET request to "/api/v2/ticket_layouts/<context>/default"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.fields[<dep_num>].field_id" should be equal to "department"
    And the JSON node "data.fields[<dep_num>].required" should be equal to 1

    And the JSON node "data.fields[<subject_num>].field_id" should be equal to "subject"
    And the JSON node "data.fields[<subject_num>].required" should be equal to 1

    And the JSON node "data.fields[<message_num>].field_id" should be equal to "message"
    And the JSON node "data.fields[<message_num>].required" should be equal to 1

    Examples:
      | context | dep_num | subject_num | message_num |
      | agent   | 1       | 3           | 4           |
      | user    | 0       | 1           | 2           |

  Scenario Outline: I check built-in fields
    Given the setting "core.use_product" is set to 1
    And the setting "core.use_ticket_priority" is set to 1
    And the setting "core.use_ticket_category" is set to 1
    And the setting "core.use_ticket_workflow" is set to 1
    And the setting "core_tickets.field_validation_ticket_prod_<context>_required" is set to <required>
    And the setting "core_tickets.field_validation_ticket_pri_<context>_required" is set to <required>
    And the setting "core_tickets.field_validation_ticket_cat_<context>_required" is set to <required>
    And the setting "core_tickets.field_validation_ticket_work_<context>_required" is set to <required>
    And the only default ticket layout exists with fields:
      | <context>_layout |
      | <field>          |

    When I send a GET request to "/api/v2/ticket_layouts/<context>/default"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.fields[<field_num>].field_id" should be equal to "<field>"
    And the JSON node "data.fields[<field_num>].required" should be equal to <required>

    Examples:
      | context | field    | field_num | required |
      | agent   | product  | 2         | 0        |
      | agent   | product  | 2         | 1        |
      | agent   | priority | 2         | 0        |
      | agent   | priority | 2         | 1        |
      | agent   | category | 2         | 0        |
      | agent   | category | 2         | 1        |
      | agent   | workflow | 2         | 0        |
      | agent   | workflow | 2         | 1        |
      | user    | product  | 0         | 0        |
      | user    | product  | 0         | 1        |
      | user    | priority | 0         | 0        |
      | user    | priority | 0         | 1        |
      | user    | category | 0         | 0        |
      | user    | category | 0         | 1        |
      | user    | workflow | 0         | 0        |
      | user    | workflow | 0         | 1        |

  Scenario Outline: I check custom fields
    Given only the following <custom_field_class> records exist:
      | #  | Type | Title      | Options                           |
      | f1 | text | Text field | {"<required_option>": <required>} |
    And the only default ticket layout exists with fields:
      | <context>_layout         |
      | <custom_field_type>_{f1} |

    When I send a GET request to "/api/v2/ticket_layouts/<context>/default"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.fields[<field_num>].field_type" should be equal to "<custom_field_type>"
    And the JSON node "data.fields[<field_num>].required" should be equal to <required>

    Examples:
      | context | custom_field_type | custom_field_class    | field_num | required_option | required |
      | agent   | ticket_field      | CustomDefTicket       | 2         | agent_required  | 0        |
      | agent   | ticket_field      | CustomDefTicket       | 2         | agent_required  | 1        |
      | agent   | user_field        | CustomDefPerson       | 2         | agent_required  | 0        |
      | agent   | user_field        | CustomDefPerson       | 2         | agent_required  | 1        |
      | agent   | org_field         | CustomDefOrganization | 2         | agent_required  | 0        |
      | agent   | org_field         | CustomDefOrganization | 2         | agent_required  | 1        |
      | user    | ticket_field      | CustomDefTicket       | 0         | required        | 0        |
      | user    | ticket_field      | CustomDefTicket       | 0         | required        | 1        |
      | user    | user_field        | CustomDefPerson       | 0         | required        | 0        |
      | user    | user_field        | CustomDefPerson       | 0         | required        | 1        |
      | user    | org_field         | CustomDefOrganization | 0         | required        | 0        |
      | user    | org_field         | CustomDefOrganization | 0         | required        | 1        |

  Scenario Outline: I check multi-brand field
    Given I have several brands
    And the only default ticket layout exists with fields:
      | <context>_layout |
      | department       |

    When I send a GET request to "/api/v2/ticket_layouts/<context>/default"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.fields[<dep_num>].field_id" should be equal to "department"
    And the JSON node "data.fields[<dep_num>].required" should be equal to 1

    And the JSON node "data.fields[<subject_num>].field_id" should be equal to "subject"
    And the JSON node "data.fields[<subject_num>].required" should be equal to 1

    And the JSON node "data.fields[<message_num>].field_id" should be equal to "message"
    And the JSON node "data.fields[<message_num>].required" should be equal to 1

    And the JSON node "data.fields[<brand_num>].field_id" should be equal to "brand"
    And the JSON node "data.fields[<brand_num>].required" should be equal to 0

    Examples:
      | context | dep_num | subject_num | message_num | brand_num |
      | agent   | 2       | 4           | 5           | 1         |
      | user    | 0       | 1           | 2           | 5         |
