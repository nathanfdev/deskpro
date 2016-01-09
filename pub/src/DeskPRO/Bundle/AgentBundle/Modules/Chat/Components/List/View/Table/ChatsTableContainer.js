import React, {Component, PropTypes} from 'react';
import { Table } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { TableHeader } from './TableHeader';
import { Row } from './Row';
import { elementsSelector } from '../../../../Selectors/list';
import { chatsSelector } from '../../../../Selectors/recordStores';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    ids: elementsSelector(state),
    chats: chatsSelector(state)
  });
})
export class ChatsTableContainer extends Component {
  static propTypes = {
    ids: PropTypes.array.isRequired,
    chats: PropTypes.object.isRequired
  };

  renderRow(id) {
    const { chats } = this.props;
    const element = chats.get(id);

    return (
      <Row key={id} element={element}/>
    );
  }

  render() {
    return (
      <Table>
        <TableHeader />
        <tbody>
        {this.props.ids.map(id => this.renderRow(id))}
        </tbody>
      </Table>
    );
  }
}
