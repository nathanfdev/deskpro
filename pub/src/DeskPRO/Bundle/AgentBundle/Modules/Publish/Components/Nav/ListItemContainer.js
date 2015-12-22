import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import * as actions from '../../Actions/publishListActions';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';

@connect(state => ({
  hash: state.Application.routing.get('hash')
}))
export class ListItemContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    activeItemId: PropTypes.string,
    label: PropTypes.string.isRequired,
    children: PropTypes.node,
    listOptions: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount() {
    const {hash, listOptions, dispatch} = this.props;
    const activeItemId = hash.get('nav') ? hash.get('nav').get(this.props.group) : null;
    console.log('Active Item ID', activeItemId);
    console.log('Item ID', this.itemId);
    console.log('Group', this.props.group);
    console.log('Hash', hash);
    if (activeItemId === this.itemId) {
      dispatch(actions.applyParams(listOptions));
    }
  }

  loadList = () => {
    const { listOptions } = this.props;
    this.props.dispatch(actions.applyParams(listOptions));
  };

  render() {
    const props = {
      groupId: 'nav',
      active: this.props.group,
      onClick: this.loadList,
      itemId: this.itemId,
      label: this.props.label,
      children: this.props.children
    };

    return (
      <ListItemStatefulContainer {...props} />
    );
  }

}
