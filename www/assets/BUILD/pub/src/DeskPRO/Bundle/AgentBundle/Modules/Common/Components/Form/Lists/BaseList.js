import React, { PropTypes } from 'react';
import { RadioList } from './RadioList';
import { CheckboxList } from './CheckboxList';

export class BaseList extends React.Component {

  static propTypes = {
    values: PropTypes.object.isRequired,
    multiple: PropTypes.bool
  };

  renderLabelComponent(value) {
    return value;
  }

  render() {
    return (
      this.props.multiple
        ? <CheckboxList {...this.props} renderLabelComponent={this.renderLabelComponent} />
        : <RadioList {...this.props} renderLabelComponent={this.renderLabelComponent} />
    );
  }
}

