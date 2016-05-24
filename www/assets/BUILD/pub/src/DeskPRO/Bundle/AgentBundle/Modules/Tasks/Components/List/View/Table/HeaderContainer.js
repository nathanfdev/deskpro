import React, { PropTypes } from 'react';
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
    if (!field) {
      return null;
    }

    if (!field.get('visible')) {
      return null;
    }

    const props = this.props;
    const columnProps = {
      ...props,
      onChange: this.onChange
    };

    switch (field.get('id')) {

      case 'id':
        return <Th title="Id" className="id-col" sort="id" {...columnProps} key={field.get('id')} />;

      case 'title':
        return <Th title="Title" className="subject-col" sort="title" {...columnProps} key={field.get('id')} />;

      case 'project':
        return <Th title="Project" sort="project" {...columnProps} key={field.get('id')} />;

      case 'date_due':
        return <Th title="Due" sort="date_due" {...columnProps} key={field.get('id')} />;

      case 'assignee':
        return <Th title="Assignee" className="agent-col" sort="assignee" {...columnProps} key={field.get('id')} />;

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
