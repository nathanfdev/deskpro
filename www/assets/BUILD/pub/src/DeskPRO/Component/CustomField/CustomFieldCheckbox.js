import PropTypes from 'prop-types';
import React from 'react';
import { withFormValue } from '@deskpro/react-forms';
import classNames from 'classnames';
import { AbstractCustomField } from './AbstractCustomField';
import { noFocusBorder } from './noFocusBorderDecorator';

export class CustomFieldCheckbox extends AbstractCustomField {

  renderDeep(choice, parentTitle = '') {
    const { name } = this.props;

    if (choice.get('children') && choice.get('children').size) {
      return choice.get('children').map(child => this.renderDeep(child, `${parentTitle}${choice.get('title')} > `));
    }

    return (
      <MultipleCheckboxWithFormValue
        key={choice.get('id')}
        select={name}
        itemValue={choice.get('id')}
        itemLabel={`${parentTitle}${choice.get('title')}`}
      />
    );
  }

  render() {
    const { config } = this.props;

    return (
      <div>
        {config.get('choices').map(choice => this.renderDeep(choice))}
      </div>
    );
  }
}

@noFocusBorder
class MultipleCheckbox extends React.Component {

  static propTypes = {
    formValue: PropTypes.object,
    itemValue: PropTypes.node,
    itemLabel: PropTypes.string
  };

  componentDidMount() {
    this.addNoFocusBorderListeners(this.el);
  }

  onClick = () => {
    const { formValue, itemValue } = this.props;
    const value = formValue.value || [];

    let newValue = [];
    if (value) {
      if (Array.isArray(value)) {
        newValue = value;
      } else {
        newValue = [value];
      }
    }

    const index = newValue.indexOf(itemValue);
    if (index !== -1) {
      newValue.splice(index, 1);
    } else {
      newValue.push(itemValue);
    }

    formValue.update(newValue);
  };

  render() {
    const { itemValue, formValue, itemLabel } = this.props;
    const value = formValue.value || [];

    return (
      <div
        className="checkbox-container"
        tabIndex={0}
        ref={(c) => { this.el = c; }}
        onClick={this.onClick}
      >
        <span className={classNames('checkbox', { checked: value.indexOf(itemValue) !== -1 })}>
          <i className="fa fa-check" />
        </span>

        {itemLabel}
      </div>
    );
  }
}

const MultipleCheckboxWithFormValue = withFormValue(MultipleCheckbox);
