import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { currentOrderBySelector, currentOrderDirSelector, tableFieldsSelector } from '../../../../Selectors/list';
import { applyOrderBy, applyOrderDir } from '../../../../Actions/listActions';

@connect(state => ({
  currentSort:  currentOrderBySelector(state),
  currentOrder: currentOrderDirSelector(state),
  fields:       tableFieldsSelector(state)
}), {
  applyOrderBy,
  applyOrderDir
})
export class HeaderContainer extends React.Component {

  static propTypes = {
    fields:        PropTypes.object.isRequired,
    applyOrderBy:  PropTypes.func.isRequired,
    applyOrderDir: PropTypes.func.isRequired
  };

  onChange = (sort, order) => {
    this.props.applyOrderBy(sort);
    this.props.applyOrderDir(order);
  };

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const fieldId = field.get('id');
    const columnProps = {
      ...this.props,
      onChange: this.onChange
    };

    switch (fieldId) {

      case 'id':
        return <Th title="Id" className="id-col" sort="id" {...columnProps} key={fieldId} />;

      case 'title':
        return <Th title="Title" className="subject-col" sort="title" {...columnProps} key={fieldId} />;

      case 'project':
        return <Th title="Project" sort="project" {...columnProps} key={fieldId} />;

      case 'date_due':
        return <Th title="Due" sort="date_due" {...columnProps} key={fieldId} />;

      case 'assignee':
        return <Th title="Assignee" className="agent-col" sort="assignee" {...columnProps} key={fieldId} />;

      default:
        return null;
    }
  }

  render() {
    const { fields } = this.props;

    return (
      <thead>
      <tr className="tickets-tabular">
        <Th className="bulk-edit-col" />

        {fields.map(field => this.renderField(field))}

      </tr>
      </thead>
    );
  }
}
