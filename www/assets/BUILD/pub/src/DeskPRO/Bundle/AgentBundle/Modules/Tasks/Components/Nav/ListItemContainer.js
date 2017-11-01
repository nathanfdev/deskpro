import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { currentNavSelector } from '../../Selectors/list';
import { setListParamsNav, loadList } from '../../Actions/listActions';
import { pureRender } from 'Ampliflux';

@connect(state => ({
  activeItemId: currentNavSelector(state)
}))

@pureRender

export class ListItemContainer extends React.Component {

  static propTypes = {
    dispatch:     PropTypes.func.isRequired,
    activeItemId: PropTypes.string,
    urlHash:      PropTypes.string.isRequired,
    listOptions:  PropTypes.object.isRequired
  };

  componentDidMount() {
    const { activeItemId } = this.props;
    if (activeItemId === urlSanitize(this.props.urlHash)) {
      this.loadList();
    }
  }

  loadList = () => {
    const { listOptions, dispatch } = this.props;

    dispatch(setListParamsNav(listOptions));
    dispatch(loadList());
  };

  render() {
    const props = this.props;
    const newProps = {
      ...props,
      groupId: 'nav',
      onClick: this.loadList,
      itemId:  urlSanitize(props.urlHash)
    };

    return (
      <ListItemStatefulContainer {...newProps} />
    );
  }
}
