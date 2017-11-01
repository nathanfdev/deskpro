import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { Nav } from './Nav';
import { initialLoad } from '../../Actions/navActions';
import { isLoadedSelector } from '../../Selectors/nav';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  isLoaded: isLoadedSelector(state)
}), {
  loadAll,
  initialLoad
})
export class NavContainer extends React.Component {

  static propTypes = {
    loadAll:     PropTypes.func.isRequired,
    initialLoad: PropTypes.func.isRequired
  };

  componentDidMount() {
    const { loadAll, initialLoad } = this.props;
    loadAll('Project');
    loadAll('TaskLabel');
    loadAll('TaskList');
    initialLoad();
  }

  render() {
    return <Nav {...this.props} />;
  }
}
