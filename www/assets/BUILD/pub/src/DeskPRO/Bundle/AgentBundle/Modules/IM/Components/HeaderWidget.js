import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';

import { List as RecentList } from './Recent/List';
import * as chatsActions from '../Actions/chatsActions';

@connect()
export class HeaderWidget extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  onClick = () => {
    this.props.dispatch(chatsActions.toggleOverlay());
  };

  render() {
    return (
      <div className="agent-ims">
        <a href="#" id="im-button" onClick={this.onClick} className="show-more">
          <span>
            IMs <i className="fa fa-angle-down" />
          </span>
        </a>
        <RecentList />
      </div>
    );
  }
}
