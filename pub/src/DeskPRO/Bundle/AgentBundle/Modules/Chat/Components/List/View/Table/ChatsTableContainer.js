import React, {Component, PropTypes} from 'react';
import { Table } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { TableHeader } from './TableHeader';
import { Row } from './Row';
import { elementsSelector } from '../../../../Selectors/list';
import { chatsSelector, peopleSelector } from '../../../../Selectors/recordStores';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    ids: elementsSelector(state),
    chats: chatsSelector(state),
    people: peopleSelector(state),
    departments: collectionSelectorFactory('Department', 'chats')(state)
  });
})
export class ChatsTableContainer extends Component {
  static propTypes = {
    ids: PropTypes.array.isRequired,
    people: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    chats: PropTypes.object.isRequired
  };

  renderRow(id) {
    const { chats, people, departments } = this.props;
    const element = chats.get(id);

    return (
      <Row key={id}
           element={element}
           author={people.get(element.get('person'))}
           agent={people.get(element.get('agent'))}
           department={departments.get(element.get('department'))}/>
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
