import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'DeskPRO/Component/Ampliflux';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';
import { applyParams } from '../../Actions/chatListActions.js';

@connect(state => ({
  activeItemId: hashStateSelectorFactory(['nav', 'active'])(state)
}))
@pureRender
export class ListItemContainer extends Component {

  static propTypes = {
    dispatch:     PropTypes.func.isRequired,
    label:        PropTypes.string.isRequired,
    listOptions:  PropTypes.object.isRequired,
    children:     PropTypes.node,
    activeItemId: PropTypes.string,
    content:      PropTypes.string.isRequired,
    hash:         PropTypes.object
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount = () => {
    const { listOptions, dispatch, activeItemId } = this.props;
    if (activeItemId === this.itemId) {
      dispatch(applyParams(listOptions));
    }
  };

  loadList = () => {
    const { dispatch, listOptions } = this.props;
    dispatch(applyParams(listOptions));
  };

  render = () => {
    const { label, children, content } = this.props;
    const props = {
      label,
      children,

      groupId: 'nav',
      active:  content,
      onClick: this.loadList,
      itemId:  this.itemId
    };

    return <ListItemStatefulContainer {...props} />;
  }
}
