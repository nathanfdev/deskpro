import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { currentOrderBySelector, currentOrderDirSelector, tableVisibleFieldsSelector } from '../../../../Selectors/list';
import { applyOrderBy, applyOrderDir } from '../../../../Actions/listActions';

@connect(state => ({
  currentSort:   currentOrderBySelector(state),
  currentOrder:  currentOrderDirSelector(state),
  visibleFields: tableVisibleFieldsSelector(state)
}))
export class HeaderContainer extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
    visibleFields: PropTypes.object.isRequired
  };

  onChange = (sort, order) => {
    this.props.dispatch(applyOrderBy(sort));
    this.props.dispatch(applyOrderDir(order));
  };

  render() {
    const props = this.props;
    const columnProps = { ...props, onChange: this.onChange };
    const isVisible = type => props.visibleFields.includes(type);

    return (
      <thead>
        <tr>
          <Th />
          <Th title="Id" sort="id" visible={isVisible('id')} {...columnProps} />
          <Th title="Title" sort="title" {...columnProps} />
          <Th title="Project" sort="project" visible={isVisible('project')} {...columnProps} />
          <Th title="Due" sort="date_due" visible={isVisible('date_due')} {...columnProps} />
          <Th title="Assignee" sort="assignee" visible={isVisible('assignee')} {...columnProps} />
        </tr>
      </thead>
    );
  }
}
