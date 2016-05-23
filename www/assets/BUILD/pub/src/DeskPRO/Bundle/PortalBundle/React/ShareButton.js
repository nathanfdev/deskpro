import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import Modal from 'DeskPRO/Component/Modal/Modal';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ShareButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      widgetLoaded: false,
      onlineAgents: Immutable.fromJS([])
    };
  }

  render() {
    let title = portalPhrases.get('portal.general.share-this');
    let modal = Modal.render();
    return modal;
  }
}