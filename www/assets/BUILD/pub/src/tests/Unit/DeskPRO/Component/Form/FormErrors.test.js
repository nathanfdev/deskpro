jest.dontMock('DeskPRO/Component/Form/FormErrors.js');

const FormErrors = require('DeskPRO/Component/Form/FormErrors.js');

describe('Form errors component', () => {
  it('Convert property path to error path', () => {
    expect(FormErrors.getErrorPath('')).toEqual(['errors']);
    expect(FormErrors.getErrorPath('name')).toEqual(['fields', 'name', 'errors']);
    expect(FormErrors.getErrorPath('person.name')).toEqual(['fields', 'person', 'fields', 'name', 'errors']);
    expect(FormErrors.getErrorPath('ticket.person.name')).toEqual(['fields', 'ticket', 'fields', 'person', 'fields', 'name', 'errors']);
    expect(FormErrors.getErrorPath('ticket_fields.5')).toEqual(['fields', 'ticket_fields', 'fields', 'ticket_fields_5', 'errors']);
  });

  it('Get errors by property path', () => {
    const formErrors = {
      fields: {
        person: {
          fields: {
            name: {
              errors: [
                {
                  code:    'required',
                  message: 'This value should not be blank.'
                }
              ]
            }
          }
        },
        ticket_fields: {
          fields: {
            ticket_fields_5: {
              errors: [
                {
                  code:    'length_too_short',
                  message: 'This value is too short. It should have 10 characters or more.'
                }
              ]
            }
          }
        }
      }
    };

    expect(FormErrors.getErrorsByPropertyPath(formErrors, 'person')).toEqual([]);
    expect(FormErrors.getErrorsByPropertyPath(formErrors, 'unknown_field')).toEqual([]);
    expect(FormErrors.getErrorsByPropertyPath(null, 'unknown_field')).toEqual([]);
    expect(FormErrors.getErrorsByPropertyPath(formErrors, 'person.name')).toEqual([
      {
        code:    'required',
        message: 'This value should not be blank.'
      }
    ]);
    expect(FormErrors.getErrorsByPropertyPath(formErrors, 'ticket_fields.5')).toEqual([
      {
        code:    'length_too_short',
        message: 'This value is too short. It should have 10 characters or more.'
      }
    ]);
  });
});
