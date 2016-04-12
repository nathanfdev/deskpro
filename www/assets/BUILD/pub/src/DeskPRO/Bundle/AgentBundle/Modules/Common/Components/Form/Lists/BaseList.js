import React, { PropTypes } from 'react';
import { RadioList } from './RadioList';
import { CheckboxList } from './CheckboxList';

export class BaseList extends React.Component {

  static propTypes = {
    multiple: PropTypes.bool,
    values:   PropTypes.object.isRequired
  };

  getKeyword() {
    return '';
  }

  renderLabel(value) {
    return value;
  }

  renderRadioList() {
    return (
      <RadioList
        {...this.props}

        renderLabel={this.renderLabel}
        getKeyword={this.getKeyword} />
    );
  }

  renderCheckboxList() {
    return (
      <CheckboxList
        {...this.props}

        renderLabel={this.renderLabel}
        getKeyword={this.getKeyword} />
    );
  }

  render() {
    return this.props.multiple ? this.renderCheckboxList() : this.renderRadioList();
  }
}
