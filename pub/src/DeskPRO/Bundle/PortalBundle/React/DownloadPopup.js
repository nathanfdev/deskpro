import React, { PropTypes } from 'react';
import { ClickOut } from 'DeskPRO/Component/ClickOut';

export class DownloadPopup extends React.Component {

  static propTypes = {
    filename: PropTypes.string,
    filesize: PropTypes.string,
    dateUploaded: PropTypes.string,
    downloadUrl: PropTypes.string,
    voteUrl: PropTypes.string,
    voteCount: PropTypes.number,
    $button: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      opened: false
    };
  }

  componentDidMount() {
    this.props.$button.on('click', this.onOpen);
  }

  onOpen = event => {
    event.preventDefault();
    this.setState({
      opened: true
    });
  };

  onClose = event => {
    event.preventDefault();
    this.setState({
      opened: false
    });
  };

  onVote = event => {
    event.preventDefault();

    const { voteUrl } = this.props;
    if (!voteUrl) {
      return;
    }

    console.log('on vote');
  };

  render() {
    const { $button, filename, filesize, downloadUrl, dateUploaded, voteCount = 0 } = this.props;

    if (!this.state.opened) {
      return null;
    }

    return (
      <div>
        <ClickOut onClickOut={this.onClose} additionalNodes={[$button]}>
          <div className="popup popup-file-download">
            <a href="#" className="cancel" onClick={this.onClose}>
              Cancel download <i className="fa fa-times"/>
            </a>

            <div className="file-icon">
              <i className="fa fa-file"/>
              <hr/>

              <div className="cudos-wrapper">
                <a className="cudos" onClick={this.onVote}>
                  <i className="fa fa-thumbs-up"/> {voteCount}
                </a>
              </div>
            </div>

            <h1>{filename}</h1>
            <h2>Uploaded on: {dateUploaded} - {filesize}</h2>

            <a href={downloadUrl} className="button" target="_blank">
              Download File
            </a>
          </div>
        </ClickOut>
        <div className="cover"></div>
      </div>
    );
  }
}
