import React, { PropTypes } from 'react';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import $ from 'jquery';
import classNames from 'classnames';

export class DownloadPopup extends React.Component {

  static propTypes = {
    filename:     PropTypes.string,
    filesize:     PropTypes.string,
    dateUploaded: PropTypes.string,
    downloadUrl:  PropTypes.string,
    voteUrl:      PropTypes.string,
    voteCount:    PropTypes.number,
    $button:      PropTypes.object,
    $voteWidget:  PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      opened:    false,
      voted:     false,
      voteCount: props.voteCount || 0
    };
  }

  componentDidMount() {
    const { $button, $voteWidget } = this.props;

    $button.on('click', this.onOpen);
    $voteWidget.on('vote', this.onVote);
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

    const { voteUrl, $voteWidget } = this.props;
    if (this.state.voted || $voteWidget.hasClass('with-voted')) {
      return;
    }

    this.setState({
      voted:     true,
      voteCount: this.state.voteCount + 1
    });

    $.post(voteUrl);
    setTimeout(() => $voteWidget.trigger('vote'), 0);
  };

  render() {
    const { $button, filename, filesize, downloadUrl, dateUploaded } = this.props;

    if (!this.state.opened) {
      return null;
    }

    return (
      <div>
        <ClickOut onClickOut={this.onClose} additionalNodes={$button}>
          <div className="popup popup-file-download">
            <a href="#" className="cancel" onClick={this.onClose}>
              Cancel download <i className="fa fa-times" />
            </a>

            <div className="file-icon">
              <i className="fa fa-file" />
              <hr />

              <div className="cudos-wrapper">
                <a className={classNames('cudos', { 'with-voted': this.state.voted })} onClick={this.onVote}>
                  <i className="fa fa-thumbs-up" /> {this.state.voteCount}
                </a>
              </div>
            </div>

            <h1>{filename}</h1>
            {dateUploaded
              ? <h2>Uploaded on: {dateUploaded} - {filesize}</h2>
              : <h2>{filesize}</h2>
            }

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
