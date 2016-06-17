import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { applyParams } from '../../Actions/publishListActions';
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
    content:       PropTypes.string.isRequired,
    children:    PropTypes.node,
    listOptions: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount = () => {
    const { hash, listOptions, dispatch, group, content } = this.props;
    console.log('Group', group);
    const activeItemId = hash.get('nav') ? hash.get('nav').get(`${content}_${group}`) : null;
    if (activeItemId === this.itemId && content === listOptions.content) {
      dispatch(applyParams(listOptions));
    }
  };

  loadList = () => {
    const { dispatch, listOptions } = this.props;
    dispatch(applyParams(listOptions));
  };

  render = () => {
    const { group, label, children, content } = this.props;
    const props = {
      label,
      children,

      groupId: 'nav',
      active:  `${content}_${group}`,
      onClick: this.loadList,
      itemId:  this.itemId
    };

    return (
      <ListItemStatefulContainer {...props} />
    );
  }

}
