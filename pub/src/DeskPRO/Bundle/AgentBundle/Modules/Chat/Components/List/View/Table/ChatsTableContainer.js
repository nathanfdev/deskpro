import React, {Component, PropTypes} from 'react';
import { Table } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { TableHeader } from './TableHeader';
import { Row } from './Row';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';
import { connect } from 'react-redux';

@connect(state => ({
  chats: collectionSelectorFactory('UserChat', 'chats')(state),
  people: collectionSelectorFactory('Person', 'chats')(state),
  departments: collectionSelectorFactory('Department', 'chats')(state)
}))
export class ChatsTableContainer extends Component {
  static propTypes = {
    people: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    chats: PropTypes.object.isRequired
  };

  renderRow(element) {
    const { people, departments } = this.props;

    return (
      <Row key={element.get('id')}
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
        {this.props.chats.map(chat => this.renderRow(chat))}
        </tbody>
      </Table>
    );
  }
}
