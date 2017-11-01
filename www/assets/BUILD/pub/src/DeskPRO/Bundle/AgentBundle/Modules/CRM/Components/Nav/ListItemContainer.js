import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { ListItemStatefulContainer } from '../../../Common/Components/NavFrame';
import { applyParams } from '../../Actions/crmListActions';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';

@connect(state => ({
  hash: state.Application.routing.get('hash')
}))

export class ListItemContainer extends Component {
  static propTypes = {
    dispatch:    PropTypes.func.isRequired,
    hash:        PropTypes.object,
    group:       PropTypes.string,
    label:       PropTypes.string.isRequired,
    children:    PropTypes.node,
    listOptions: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount() {
    const { hash, listOptions, dispatch, group } = this.props;
    const activeItemId = hash.get('nav') ? hash.get('nav').get(group) : null;
    if (activeItemId === this.itemId) {
      dispatch(applyParams(listOptions));
    }
  }

  loadList = () => {
    const { listOptions } = this.props;
    this.props.dispatch(applyParams(listOptions));
  };

  render() {
    const props = {
      groupId:  'nav',
      active:   this.props.group,
      onClick:  this.loadList,
      itemId:   this.itemId,
      label:    this.props.label,
      children: this.props.children
    };

    return (
      <ListItemStatefulContainer {...props} />
    );
  }
}
