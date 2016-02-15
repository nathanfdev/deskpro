import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { Nav } from './Nav';
import { initialLoad } from '../../Actions/navActions';
import { isLoadedSelector } from '../../Selectors/nav';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  isLoaded: isLoadedSelector(state)
}))
export class NavContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    props.dispatch(loadAll('Project'));
    props.dispatch(loadAll('TaskLabel'));
    props.dispatch(loadAll('TaskList'));

    props.dispatch(initialLoad());
  }

  render() {
    return <Nav {...this.props} />;
  }
}
