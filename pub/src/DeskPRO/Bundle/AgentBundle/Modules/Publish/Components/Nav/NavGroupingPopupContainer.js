import React, { Component, PropTypes } from 'react';
import { ListGroupingControl } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';


export class NavGroupingPopupContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    closeGroupingVisibility: PropTypes.func.isRequired,
    title: PropTypes.string.isRequired,
    groupedBy: PropTypes.string.isRequired,
    attachTo: PropTypes.object.isRequired,
    visible: PropTypes.bool.isRequired
  };

  static groupingOptions = [
    { value: 'category', label: 'Category' },
    { value: 'author', label: 'Author' },
    { value: 'period_created', label: 'Created' },
    { value: 'period_updated', label: 'Updated' }
  ];

  render() {
    const { attachTo, title, groupedBy, visible, closeGroupingVisibility } = this.props;
    const selected = NavGroupingPopupContainer.groupingOptions.find(item => item.value === groupedBy);

    return (
      <ListGroupingControl visible={visible}
                           title={title}
                           options={NavGroupingPopupContainer.groupingOptions}
                           onChange={this.applyFilterEditing}
                           close={closeGroupingVisibility}
                           selected={selected.label}
                           attachTo={attachTo}/>
    );
  }
}