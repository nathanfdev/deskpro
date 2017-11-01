import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Td, TdId, PersonInTable }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';

export class Row extends Component {
  static propTypes = {
    element:    PropTypes.object.isRequired,
    author:     PropTypes.object.isRequired,
    agent:      PropTypes.object.isRequired,
    department: PropTypes.object,
    fields:     PropTypes.object.isRequired
  };

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const { element, author, agent, department } = this.props;
    const fieldId = field.get('id');

    switch (fieldId) {

      case 'id':
        return <TdId key={fieldId}>{element.get(fieldId)}</TdId>;

      case 'person':
        return <Td key={fieldId}><PersonInTable person={author} /></Td>;

      case 'agent':
        return <Td key={fieldId} className="agent-col"><PersonInTable person={agent} /></Td>;

      case 'department':
        return <Td key={fieldId}>{department ? department.get('title') : ''}</Td>;

      case 'subject':
        return <Td key={fieldId} className="item-title">{element.get(fieldId)}</Td>;

      default:
        return <Td key={fieldId}>{element.get(fieldId)}</Td>;
    }
  }

  render() {
    const { fields } = this.props;

    return (
      <tr>
        {fields.map(field => this.renderField(field))}
      </tr>);
  }
}
