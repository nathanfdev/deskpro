import React from 'react';
import { connect } from 'react-redux';
import { FilterBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

@connect()
export class FilterByContainer extends React.Component {

  render() {
    return (
      <FilterBy {...this.props} />
    );
  }
}
