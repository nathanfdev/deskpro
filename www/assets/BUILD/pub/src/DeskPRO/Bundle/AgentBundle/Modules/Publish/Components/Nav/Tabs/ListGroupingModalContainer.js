import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { ListGroupingModal } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { changeListGroupingActionFactory } from '../../../Actions/publishNavActions';

@connect()
export class ListGroupingModalContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  static groupingOptions = [
    { value: 'category', label: 'Category' },
    { value: 'author', label: 'Author' },
    { value: 'period_created', label: 'Created' },
    { value: 'period_updated', label: 'Updated' }
  ];

  render() {
    const { attachTo, content, selected, visible, close } = this.props;
    const options  = ListGroupingModalContainer.groupingOptions;
    const apply = (groupedBy) => this.props.dispatch(changeListGroupingActionFactory(content)(groupedBy));
    const props = {attachTo, visible, title: content, options, selected, apply, close};

    return <ListGroupingModal {...props} />;
  }
}
