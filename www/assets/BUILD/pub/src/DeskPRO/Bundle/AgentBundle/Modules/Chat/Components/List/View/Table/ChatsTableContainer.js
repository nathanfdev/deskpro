import React, { Component, PropTypes } from 'react';
import { Table } from '../../../../../Common/Components/ListFrame';
import { TableHeader } from './TableHeader';
import { Row } from './Row';
import { collectionSelectorFactory } from '../../../../../../../AppBundle/Modules/RecordsStore';
import { connect } from 'react-redux';

@connect(state => ({
  chats:       collectionSelectorFactory('UserChat', 'chats')(state),
  people:      collectionSelectorFactory('Person', 'chats')(state),
  departments: collectionSelectorFactory('Department', 'all_chats')(state)
}))

export class ChatsTableContainer extends Component {
  static propTypes = {
    people:      PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    chats:       PropTypes.object.isRequired,
    fields:      PropTypes.object.isRequired
  };

  renderRow(element) {
    const { people, departments, fields } = this.props;

    return (
      <Row
        key={element.get('id')}
        element={element}
        author={people.get(element.get('person'))}
        agent={people.get(element.get('agent'))}
        department={departments.get(element.get('department'))}
        fields={fields}
      />
    );
  }

  render() {
    return (
      <Table>
        <TableHeader fields={this.props.fields} />
        <tbody>
          {this.props.chats.entrySeq().map(([id, chat]) => this.renderRow(chat))}
        </tbody>
      </Table>
    );
  }
}
