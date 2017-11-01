import PropTypes from 'prop-types';
import React from 'react';
import { withFormValue, Input } from 'react-forms';
import { AbstractCustomField } from './AbstractCustomField';
import { noFocusBorder } from './noFocusBorderDecorator';

export class CustomFieldRadio extends AbstractCustomField {

  renderDeep(choice, parentTitle = '') {
    const { name } = this.props;

    if (choice.get('children') && choice.get('children').size) {
      return choice.get('children').map(child => this.renderDeep(child, `${parentTitle}${choice.get('title')} > `));
    }

    return (
      <RadioButtonWithFormValue
        key={choice.get('id')}
        select={name}
        itemValue={choice.get('id')}
        itemLabel={`${parentTitle}${choice.get('title')}`}
      />
    );
  }

  render() {
    return (
      <div className="dp-choice-widget-group">
        {this.props.config.get('choices').map(choice => this.renderDeep(choice))}
      </div>
    );
  }
}

@noFocusBorder
class RadioButton extends React.Component {

  static propTypes = {
    formValue: PropTypes.object,
    itemValue: PropTypes.any,
    itemLabel: PropTypes.string
  };

  componentDidMount() {
    this.addNoFocusBorderListeners(this.el);
  }

  onClick = () => {
    const { itemValue, formValue } = this.props;
    formValue.update(itemValue);
  };

  render() {
    const { itemValue, itemLabel, formValue } = this.props;

    return (
      <div tabIndex="0" className="radio-button" ref={(c) => { this.el = c; }}>
        <Input type="radio" value={itemValue} checked={formValue.value === itemValue} />
        <label className="title" onClick={this.onClick}>{itemLabel}</label>
      </div>
    );
  }
}

const RadioButtonWithFormValue = withFormValue(RadioButton);
