import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';

export class LabelsList extends React.Component {

  static propTypes = {
    values: PropTypes.object
  };

  render() {
    const { values = [] } = this.props;
    console.log(values);

    const options = values.map(value => ({
      label: value.get('label'),
      value: value.get('id'),
      keyword: value.get('label')
    }));

    return (
      <CheckboxList {...this.props} options={options} />
    );
  }
}
