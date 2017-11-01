import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Header } from './Header';
import { chatLoadedSelector } from '../../../../Selectors/chat';

@connect(state => ({
  chatLoaded: chatLoadedSelector(state)
}))
export class HeaderContainer extends React.Component {

  static propTypes = {
    chatLoaded: PropTypes.bool
  };

  render() {
    return this.props.chatLoaded ? <Header {...this.props} /> : null;
  }
}
