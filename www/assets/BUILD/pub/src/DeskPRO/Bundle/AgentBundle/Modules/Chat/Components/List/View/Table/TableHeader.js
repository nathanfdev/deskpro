import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';

export class TableHeader extends Component {

  static propTypes = {
    fields: PropTypes.object.isRequired
  };

  renderFieldHeader = field => {
    if (!field || !field.get('visible')) {
      return null;
    }
    return <Th key={field.get('id')} title={field.get('title')} />;
  };

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
