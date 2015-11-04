import React from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

@connect()
export class OrderByContainer extends React.Component {

  render() {
    return (
      <OrderBy {...this.props} />
    );
  }
}
