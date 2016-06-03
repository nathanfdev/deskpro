import React, { Component, PropTypes } from 'react';
import { Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';

export class TableHeader extends Component {

  static propTypes = {
    fields: PropTypes.object.isRequired
  };

  renderFieldHeader(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const fieldId = field.get('id');

    switch (fieldId) {
      default:
        return <Th key={fieldId}>{field.get(fieldId)}</Th>;
    }
  }

  render() {
    const { fields } = this.props;

    return (
      <thead>
        <tr>
          {fields.map(field => this.renderFieldHeader(field))}
        </tr>
      </thead>
    );
  }
}
