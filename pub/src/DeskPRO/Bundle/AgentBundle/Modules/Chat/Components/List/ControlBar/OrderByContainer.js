import React from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

@connect(state => ({
  sortOptions: state.Chat.list.get('sortOptions').toJS(),
  order:       state.Chat.list.get('order')
}))
export class OrderByContainer extends React.Component {

  render() {
    return (
      <OrderBy {...this.props} />
    );
  }
}
