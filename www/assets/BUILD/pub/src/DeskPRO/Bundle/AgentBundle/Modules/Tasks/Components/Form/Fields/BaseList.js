import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';

export class BaseList extends React.Component {

  static propTypes = {
    values: PropTypes.object.isRequired
  };

  renderLabel(value) {
    return value;
  }

  getKeyword() {
    return '';
  }

  render() {
    return (
      <CheckboxList {...this.props} renderLabel={this.renderLabel} getKeyword={this.getKeyword} />
    );
  }
}
