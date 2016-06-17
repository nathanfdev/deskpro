import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'Ampliflux';
import { ListItem, ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';
import { applyParams } from '../../Actions/chatListActions.js';

@connect(state => ({
  hash: state.Application.routing.get('hash')
}))
@pureRender
export class ListItemContainer extends Component {

  static propTypes = {
    dispatch:    PropTypes.func.isRequired,
    count:       PropTypes.number.isRequired,
    label:       PropTypes.string.isRequired,
    active:      PropTypes.string.isRequired,
    listOptions: PropTypes.object.isRequired,
    hash:        PropTypes.object
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount = () => {
    const { hash, listOptions, dispatch, active } = this.props;
    const activeItemId = hash.get('nav') ? hash.get('nav').get(active) : null;

    if (activeItemId === this.itemId) {
      dispatch(applyParams(listOptions));
    }
  };

  loadList = () => {
    const { dispatch, listOptions } = this.props;
    dispatch(applyParams(listOptions));
  };

  render = () => {
    const { label, count, active } = this.props;
    const props = {
      label,
      active,

      groupId: 'nav',
      onClick: this.loadList,
      itemId:  this.itemId
    };

    return (
      <ListItemStatefulContainer {...props}>
        <ListItem count={count} label={label} />
      </ListItemStatefulContainer>
    );
  }
}
