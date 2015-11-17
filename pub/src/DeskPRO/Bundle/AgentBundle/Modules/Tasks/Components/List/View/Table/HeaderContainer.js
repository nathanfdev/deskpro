import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { currentSortSelector, currentOrderSelector, tableVisibleFieldsSelector } from '../../../../Selectors/list';
import { applySort, applyOrder } from '../../../../Actions/listActions';

@connect(state => ({
  currentSort: currentSortSelector(state),
  currentOrder: currentOrderSelector(state),
  visibleFields: tableVisibleFieldsSelector(state)
}))
export class HeaderContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    visibleFields: PropTypes.object.isRequired
  };

  onChange = (sort, order) => {
    this.props.dispatch(applySort(sort));
    this.props.dispatch(applyOrder(order));
  };

  render() {
    const props = this.props;
    const columnProps = {
      ...props,
      onChange: this.onChange
    };

    const isVisible = type => props.visibleFields.includes(type);

    return (
      <thead>
        <tr>
          <Th />
          <Th title="Id" sort="id" visible={isVisible('id')} {...columnProps} />
          <Th title="Title" sort="title" visible={isVisible('title')} {...columnProps}  />
          <Th title="Project" sort="project" visible={isVisible('project')} {...columnProps} />
          <Th title="Due" sort="date_due" visible={isVisible('date_due')} {...columnProps} />
          <Th title="Assignee" sort="assignee" visible={isVisible('assignee')} {...columnProps} />
        </tr>
      </thead>
    );
  }
}
