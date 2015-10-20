import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

import { List as RecentList } from './Recent/List';
// ui
import * as uiActions from '../Actions/uiActions';

@connect()
export class HeaderWidget extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  onClick = () => {
    this.props.dispatch(uiActions.toggleOverlay());
  };

  render() {
    return (
      <div className="agent-ims">
        <a href="#" id="im-button" onClick={this.onClick} className="show-more">
            <span>
                IMs <i className="fa fa-angle-down"></i>
            </span>
        </a>
        <RecentList />
      </div>
    );
  }
}
