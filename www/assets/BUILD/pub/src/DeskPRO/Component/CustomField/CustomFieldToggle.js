import React from 'react';
import PropTypes from 'prop-types';
import { Field } from '@deskpro/react-forms';
import classNames from 'classnames';
import { AbstractCustomField } from './AbstractCustomField';
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
    value:    PropTypes.node,
    onChange: PropTypes.func
  };

  componentDidMount() {
    this.addNoFocusBorderListeners(this.element);
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
        ref={(c) => { this.element = c; }}
        onClick={this.onClick}
      >
        <span className={classNames('checkbox', { checked: value })}>
          <i className="fa fa-check" />
        </span>
      </div>
    );
  }
}
