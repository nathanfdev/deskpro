import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';

export class DepartmentsList extends React.Component {

  static propTypes = {
    values: PropTypes.object
  };

  render() {
    const { values = [] } = this.props;
    const options = values.map(value => ({
      label: value.get('title'),
      value: value.get('id')
    }));

    return (
      <CheckboxList {...this.props} options={options} />
    );
  }
}
