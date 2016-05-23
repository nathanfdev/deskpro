import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import Modal from 'DeskPRO/Component/Modal/Modal';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ShareButton extends React.Component {
  render() {
    return (
      <Modal ref="modal" title={portalPhrases.get('portal.general.share-this')}>
        <div>
          Testing modal window
        </div>
      </Modal>
    );
  }
}