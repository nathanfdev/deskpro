import PropTypes from 'prop-types';
import React from 'react';
import { Field as BaseField, ErrorList as BaseErrorList } from 'react-forms';
import { getErrorsByPropertyPath } from 'DeskPRO/Component/Form/FormErrors';
import classNames from 'classnames';

function getFieldErrors(formValue) {
  const propertyPath = formValue.keyPath.join('.');
  const formErrors   = formValue.root._errorList;    // eslint-disable-line no-underscore-dangle

  return getErrorsByPropertyPath(formErrors, propertyPath);
}

export class SemanticError extends React.Component {

  static propTypes = {
    error: PropTypes.object
  };

  render() {
    return (
      <div className="ui basic red pointing prompt label transition visible">
        {this.props.error.message}
      </div>
    );
  }
}

class SemanticErrorList extends BaseErrorList {

  constructor(props) {
    super(props);
    this.constructor.stylesheet.Error = SemanticError;
  }

  render() {
    const { Root, Error } = this.constructor.stylesheet;
    const errors = getFieldErrors(this.formValue);
    const error = errors.length > 0 && errors.pop();

    return (
      <Root {...this.props}>
        {error && <Error error={error} />}
      </Root>
    );
  }
}

export class Field extends BaseField {

  static propTypes = {
    select:           PropTypes.string,
    className:        PropTypes.string,
    help:             PropTypes.string,
    onChangeCallback: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.constructor.stylesheet.ErrorList = SemanticErrorList;
  }

  onChangeField = (e) => {
    this.onChange(e);

    const { onChangeCallback, select } = this.props;
    if (onChangeCallback) {
      onChangeCallback(e, select);
    }
  };

  render() {
    let { children } = this.props;

    const { Input, label, help, className } = this.props;
    const { Root, ErrorList, Label, InputWrapper } = this.props.stylesheet || this.constructor.stylesheet;
    const { dirty } = this.state;
    const { schema = {}, value, params = {} } = this.formValue;
    const showErrors = dirty || params.forceShowErrors;

    if (!children) {
      children = <Input value={value} onChange={this.onChangeField} />;
    } else {
      children = React.cloneElement(React.Children.only(children), { value, onChange: this.onChangeField });
    }
    return (
      <Root
        onBlur={this.onBlur}
        className={classNames('field', className, { error: getFieldErrors(this.formValue).length })}
      >
        <Label label={label} schema={schema} />
        {help && <p className="help">{help}</p>}
        <InputWrapper>
          {children}
        </InputWrapper>
        <ErrorList
          hideNonForced={!showErrors}
          formValue={this.formValue}
        />
      </Root>
    );
  }
}
