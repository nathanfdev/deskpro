import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';

export class DepartmentsList extends React.Component {

  static propTypes = {
    values: PropTypes.object
  };

  renderLabel(value) {
    return (
      value.get('title')
    );
  }

  render() {
    return (
      <CheckboxList {...this.props} renderLabel={this.renderLabel} />
    );
  }
}
