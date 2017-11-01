import PropTypes from 'prop-types';
import React from 'react';
import { Menu } from './Menu';
import { Content } from './Content';
import * as AppActions from '../../Actions/appActions';

export class Preferences extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  close = () => {
    this.props.dispatch(AppActions.closePreferences());
  };

  render() {
    const { dpWindow, dispatch } = this.props;

    return (
      <section className="popup no-footer" id="popup">
        <header>
          <h1>Account Preferences</h1>
          <div className="controls">
            <a href="#" onClick={this.close}><i className="fa fa-times" /></a>
          </div>
        </header>

        <Menu dpWindow={dpWindow} dispatch={dispatch} />
        <Content dpWindow={dpWindow} />

      </section>
    );
  }
}
