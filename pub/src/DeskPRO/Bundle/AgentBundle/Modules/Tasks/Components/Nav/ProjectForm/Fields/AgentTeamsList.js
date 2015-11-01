import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';

export class AgentTeamsList extends React.Component {

  static propTypes = {
    values: PropTypes.object
  };

  render() {
    const { values = [] } = this.props;
    const options = values.map(value => ({
      label: value.get('name'),
      value: value.get('id')
    }));

    return (
      <CheckboxList {...this.props} options={options} />
    );
  }
}
