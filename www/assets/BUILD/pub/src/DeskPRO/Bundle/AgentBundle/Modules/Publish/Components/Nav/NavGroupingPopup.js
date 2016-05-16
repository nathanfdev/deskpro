import React, { Component, PropTypes } from 'react';
import { ListGroupingControlContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { changeListGrouping } from '../../Actions/publishNavActions';

export class NavGroupingPopup extends Component {

  static propTypes = {
    closeGroupingVisibility: PropTypes.func.isRequired,
    content:                 PropTypes.string.isRequired,
    groupedBy:               PropTypes.string.isRequired,
    attachTo:                PropTypes.object.isRequired,
    visible:                 PropTypes.bool.isRequired
  };

  static groupingOptions = [
    { value: 'category', label: 'Category' },
    { value: 'author', label: 'Author' },
    { value: 'period_created', label: 'Created' },
    { value: 'period_updated', label: 'Updated' }
  ];

  render() {
    const { attachTo, content, groupedBy, visible, closeGroupingVisibility } = this.props;
    const options  = NavGroupingPopup.groupingOptions;
    const selected = options.find(item => item.value === groupedBy);

    return (
      <ListGroupingControlContainer
        visible={visible}
        content={content}
        options={options}
        changeListGrouping={changeListGrouping}
        closeGroupingVisibility={closeGroupingVisibility}
        selected={selected.value}
        attachTo={attachTo}
      />
    );
  }
}
