import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import { Modal } from '@deskpro/react-components';
import UnsplashImageBrowser from './UnsplashImageBrowser';

export default class UnsplashImageModal extends React.Component {
  static propTypes = {
    closeModal:  PropTypes.func,
    selectImage: PropTypes.func,
  };

  render() {
    const { selectImage, closeModal } = this.props;
    return (
      <Modal
        closeModal={closeModal}
        title={<FormattedMessage id="agent.publish.image_browser" />}
      >
        <UnsplashImageBrowser
          closeModal={closeModal}
          selectImage={selectImage}
        />
      </Modal>
    );
  }
}
