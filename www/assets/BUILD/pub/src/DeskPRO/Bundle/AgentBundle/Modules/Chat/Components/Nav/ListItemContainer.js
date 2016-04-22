import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'Ampliflux';
import { ListItem, ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';
import { applyParams } from '../../Actions/chatListActions.js';

@connect(state => ({
  hash: state.Application.routing.get('hash'),
}))
@pureRender
export class ListItemContainer extends Component {

  static propTypes = {
    dispatch:    PropTypes.func.isRequired,
    count:       PropTypes.number.isRequired,
    group:       PropTypes.string.isRequired,
    groupBy:     PropTypes.string.isRequired,
    label:       PropTypes.string.isRequired,
    listOptions: PropTypes.object.isRequired,
    hash:        PropTypes.object,
    onClick:     PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount() {
    const { hash, listOptions, dispatch } = this.props;
    const activeItemId = hash.get('nav') ? hash.get('nav').get('active') : null;

    if (activeItemId === this.itemId) {
      dispatch(applyParams(listOptions));
    }
  }

  loadList = () => {
    const { dispatch, listOptions } = this.props;
    dispatch(applyParams(listOptions));
  };

  render() {
    const { label, count } = this.props;
    const props = {
      groupId: 'nav',
      onClick: this.loadList,
      itemId:  this.itemId,
      label
    };

    return (
      <ListItemStatefulContainer {...props}>
        <ListItem count={count} label={label}/>
      </ListItemStatefulContainer>
    );
  }
}
