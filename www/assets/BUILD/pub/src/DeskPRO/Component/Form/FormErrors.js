import React, { PropTypes } from 'react';

/**
 * Converts form property path to form validator path.
 *
 * 'person.name'     => ['fields', 'person', 'fields', 'name', 'errors']
 * 'ticket_fields.5' => ['fields', 'ticket_fields', 'fields', 'ticket_fields_5', 'errors']
 *
 * @param {string} propertyPath Form property path.
 * @returns {Array}
 */
export function getErrorPath(propertyPath) {
  let arrayPath = [];
  if (propertyPath && typeof propertyPath === 'string') {
    arrayPath = propertyPath.split('.');
  }

  const errorPath = [];
  let parentField;
  arrayPath.forEach(field => {
    const errorField = !parentField || isNaN(field) ? field : `${parentField}_${field}`;
    parentField = errorField;

    errorPath.push('fields');
    errorPath.push(errorField);
  });

  if (errorPath.length) {
    errorPath.push('errors');
  }

  return errorPath;
}

/**
 * Returns form errors by property path.
 *
 * @param {Object} formErrors
 * @param {string} propertyPath
 */
export function getErrorsByPropertyPath(formErrors, propertyPath) {
  const iterator = (childErrors, childErrorPath) => {
    if (childErrorPath.length > 0) {
      const errorField = childErrorPath.splice(0, 1)[0];
      if (childErrors && typeof childErrors === 'object' && childErrors.hasOwnProperty(errorField)) {
        return childErrorPath.length ? iterator(childErrors[errorField], childErrorPath) : childErrors[errorField];
      }
    }

    return [];
  };

  return iterator(formErrors, getErrorPath(propertyPath));
}

export function getFormDataErrors(formData) {
  return formData._errorList.errors;
}

/**
 * @deprecated use getErrorsByPropertyPath instead
 */
export function getErrors(response, name) {
  const errors = response && response.fields || {};
  return errors[name] ? errors[name].errors : [];
}

/**
 * @deprecated use getErrorsByPropertyPath instead
 */
export function getError(response, name) {
  const error = getErrors(response, name)[0];
  return error ? error.message : null;
}

/**
 * @deprecated use getErrorsByPropertyPath instead
 */
export function getLastError(response, name) {
  const errors = getErrors(response, name);
  const error = errors.length ? errors.slice(-1)[0] : null;

  return error ? error.message : null;
}

export function hasErrors(formErrors, propertyPath) {
  return getErrorsByPropertyPath(formErrors, propertyPath).length > 0;
}

export class FieldErrors extends React.Component {

  static propTypes = {
    errors:    PropTypes.object,
    name:      PropTypes.string,
    className: PropTypes.string
  };

  render() {
    const { errors, name, className = 'error' } = this.props;

    return (
      <ul className={className}>
        {getErrorsByPropertyPath(errors, name).map((error, index) => <li key={index}>{error.message}</li>)}
      </ul>
    );
  }
}
