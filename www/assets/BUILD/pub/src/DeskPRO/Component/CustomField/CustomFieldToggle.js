import PropTypes from 'prop-types';
import React from 'react';
import { AbstractCustomField } from './AbstractCustomField';
import { Field } from 'react-forms';
import classNames from 'classnames';
import { noFocusBorder } from './noFocusBorderDecorator';

export class CustomFieldToggle extends AbstractCustomField {

  render() {
    const { name, config } = this.props;

    return (
      <Field select={name}>
        <Checkbox key={config.get('id')} />
      </Field>
    );
  }
}

@noFocusBorder
class Checkbox extends React.Component {

  static propTypes = {
    value:    PropTypes.any,
    label:    PropTypes.string,
    onChange: PropTypes.func
  };

  componentDidMount() {
    this.addNoFocusBorderListeners(this.refs.el);
  }

  onClick = () => {
    const { value, onChange } = this.props;
    onChange(!value);
  };

  render() {
    const { value } = this.props;

    return (
      <div
        className="checkbox-container"
        tabIndex={0}
        ref="el"
        onClick={this.onClick}
      >
        <span className={classNames('checkbox', { checked: value })}>
          <i className="fa fa-check" />
        </span>
      </div>
    );
  }
}
