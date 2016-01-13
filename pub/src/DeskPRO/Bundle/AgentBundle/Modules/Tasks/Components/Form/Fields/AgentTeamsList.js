import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';

export class AgentTeamsList extends React.Component {

  static propTypes = {
    values: PropTypes.object
  };

  renderLabel(value) {
    return (
      value.get('name')
    );
  }

  render() {
    return (
      <CheckboxList {...this.props} renderLabel={this.renderLabel} />
    );
  }
}
