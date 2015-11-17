import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { currentSortSelector, currentOrderSelector } from '../../../../Selectors/list';
import { applySort, applyOrder } from '../../../../Actions/listActions';

@connect(state => ({
  currentSort: currentSortSelector(state),
  currentOrder: currentOrderSelector(state)
}))
export class HeaderContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
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

    return (
      <thead>
        <tr>
          <Th />
          <Th title="Title" />
          <Th title="Project" sort="project" {...columnProps} />
          <Th title="Due" sort="date_due" {...columnProps} />
          <Th title="Assignee" sort="assignee" {...columnProps} />
        </tr>
      </thead>
    );
  }
}
