import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { SlicedString } from '../../../../../Common/Components/SlicedString';
import { Table, Th, Td, TdId, PersonInTable } from '../../../../../Common/Components/ListFrame';
import { intlShape, FormattedRelative } from 'react-intl';

export class FeedbackTable extends Component {

  static propTypes = {
    intl:                     intlShape.isRequired,
    elements:                 PropTypes.object.isRequired,
    people:                   PropTypes.object.isRequired,
    feedback:                 PropTypes.object.isRequired,
    feedbackStatusCategories: PropTypes.object,
    feedbackComments:         PropTypes.object.isRequired,
    feedbackCategories:       PropTypes.object.isRequired,
    feedbackTypes:            PropTypes.object.isRequired,
    fields:                   PropTypes.object.isRequired,
    orderBy:                  PropTypes.string.isRequired,
    orderDir:                 PropTypes.string.isRequired,
    applyParams:              PropTypes.func.isRequired
  };

  sortTable = (orderBy, orderDir) => {
    this.props.applyParams({ order_by: orderBy, order_dir: orderDir });
  };

  renderFieldHeader(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const fieldId = field.get('id');
    const { orderBy, orderDir } = this.props;

    switch (fieldId) {
      case 'id':
      case 'title':
      case 'person':
      case 'num_ratings':
      case 'date_created':
        return (
          <Th
            key={fieldId}
            sort={fieldId}
            title={field.get('title')}
            orderDir={orderDir}
            orderBy={orderBy}
            onChange={this.sortTable}
          />
        );

      default:
        return <Th key={fieldId} title={fieldId} />;
    }
  }

  renderField(field, element) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const fieldId = field.get('id');
    const { people, feedbackTypes } = this.props;

    switch (fieldId) {
      case 'id':
        return <TdId key={fieldId}>{element.get(fieldId)}</TdId>;

      case 'title':
      case 'content':
        return (
          <Td key={fieldId} className="item-title">
            <a href="#"><SlicedString string={element.get(fieldId)} /></a>
          </Td>
        );

      case 'status_category':
        return <Td key={fieldId}>{this.renderStatus(element.get(fieldId))}</Td>;

      case 'person':
        return <Td key={fieldId}><PersonInTable person={people.get(element.get(fieldId))} /></Td>;

      case 'category':
        return <Td key={fieldId}>{this.renderCategory(element.get('id'))}</Td>;

      // wtf?
      case 'type':
        return <Td key={fieldId}>{feedbackTypes.get(element.get('category')).get('title')}</Td>;

      case 'date_created':
        return (
          <Td key={fieldId}>
            <div className="dpw--timer"><FormattedRelative value={element.get(fieldId)} /></div>
          </Td>
        );

      default:
        return <Td key={fieldId}>{element.get(fieldId)}</Td>;
    }
  }

  renderStatus(statusCategory) {
    const { feedbackStatusCategories } = this.props;
    if (feedbackStatusCategories.get(statusCategory)) {
      return feedbackStatusCategories.get(statusCategory).get('title');
    }
    return null;
  }

  renderCategory(id) {
    const { feedbackCategories } = this.props;
    if (feedbackCategories) {
      return feedbackCategories.get(id) ? feedbackCategories.get(id).get('input') : null;
    }
    return null;
  }

  renderRow(id) {
    const { feedback, fields } = this.props;
    const element = feedback.get(id);

    return (
      <tr key={id}>
        {fields.map(field => this.renderField(field, element))}
      </tr>
    );
  }

  render() {
    const { elements, orderBy, orderDir, fields } = this.props;

    return (
      <Table>
        <thead>
          <tr>
            {fields.map(field => this.renderFieldHeader(field))}
          </tr>
        </thead>
        <tbody>
          {elements.map(id => this.renderRow(id))}
        </tbody>
      </Table>
    );
  }
}
