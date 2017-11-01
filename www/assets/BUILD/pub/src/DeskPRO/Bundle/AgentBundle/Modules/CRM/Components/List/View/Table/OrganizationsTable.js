import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

@injectIntl
export class OrganizationsTable extends Component {

  static propTypes = {
    intl:            intlShape.isRequired,
    organizations:   PropTypes.object,
    sortTable:       PropTypes.func.isRequired,
    currentOrderBy:  PropTypes.string.isRequired,
    currentOrderDir: PropTypes.string.isRequired,
    fields:          PropTypes.object.isRequired
  };

  renderHeaderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const { currentOrderDir, currentOrderBy, sortTable } = this.props;

    return (
      <Th
        key={field.get('id')}
        sort={field.get('id')}
        title={field.get('title')}
        orderDir={currentOrderDir}
        orderBy={currentOrderBy}
        onChange={sortTable}
      />
    );
  }

  renderField(field, element) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const fieldId = field.get('id');
    switch (fieldId) {

      case 'id':
        return <TdId key={fieldId}>{element.get(fieldId)}</TdId>;

      case 'date_created':
        return (
          <Td key={fieldId}>
            <div className="dpw--timer">
              <FormattedRelative value={element.get(fieldId)} />
            </div>
          </Td>
        );

      case 'summary':
      case 'name':
        return (
          <Td className="item-title" key={fieldId}>
            <a href="#">
              <SlicedString string={element.get(fieldId)} />
            </a>
          </Td>
        );

      default:
        return <Td key={fieldId}>{element.get(fieldId)}</Td>;
    }
  }

  render() {
    const { organizations, fields } = this.props;
    return (
      <Table>
        <thead>
          <tr>
            {fields.map(field => this.renderHeaderField(field))}
          </tr>
        </thead>
        <tbody>
          {organizations && organizations.map((element, index) =>
            <tr key={index}>
              {fields.map(field => this.renderField(field, element))}
            </tr>
        )}
        </tbody>
      </Table>
    );
  }
}
