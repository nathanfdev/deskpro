import PropTypes from 'prop-types';
import React from 'react';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { connect } from 'react-redux';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';
import { PopupWindow } from './Popup/PopupWindow';

@connect(state => ({
  user:      meSelector(state),
  popupOpen: state.ExternalEvents.popup.get('popupOpen'),
  popupData: state.ExternalEvents.popup.get('popupData'),
  state
}))

export class ExternalEventsContainer extends SeparateComponent {

  static propTypes = {
    user:      PropTypes.object.isRequired,
    dispatch:  PropTypes.func.isRequired,
    popupOpen: PropTypes.bool.isRequired,
    popupData: PropTypes.object,
  };

  static getType() {
    return 'ExternalEvents';
  }

  render() {
    const elements = [];
    if (this.props.popupOpen) {
      elements.push(<PopupWindow data={this.props.popupData} dispatch={this.props.dispatch} />);
    }

    return elements;
  }
}

export default ExternalEventsContainer;
