import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'DeskPRO/Component/Ampliflux';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { routingStateSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';
import { applyParams } from '../../Actions/listActions.js';

@connect(state => ({
  hash: routingStateSelector(state)
}))
@pureRender
export class ListItemContainer extends Component {

  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
    label:         PropTypes.string.isRequired,
    listOptions:   PropTypes.object.isRequired,
    children:      PropTypes.node,
    activeItemId:  PropTypes.string,
    parentCountId: PropTypes.string.isRequired,
    hash:          PropTypes.object
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount = () => {
    const { listOptions, dispatch, hash, parentCountId } = this.props;
    const activeItemId = hash.get('nav') ? hash.get('nav').get(parentCountId) : null;
    if (activeItemId === this.itemId) {
      dispatch(applyParams(listOptions));
    }
  };

  loadList = () => {
    const { dispatch, listOptions } = this.props;
    dispatch(applyParams(listOptions));
  };

  render = () => {
    const { label, children, parentCountId } = this.props;
    const props = {
      label,
      children,

      groupId: 'nav',
      active:  parentCountId,
      onClick: this.loadList,
      itemId:  this.itemId
    };

    return <ListItemStatefulContainer {...props} />;
  }
}
