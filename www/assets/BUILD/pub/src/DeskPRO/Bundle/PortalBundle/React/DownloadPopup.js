import PropTypes from 'prop-types';
import React from 'react';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import $ from 'jquery';
import classNames from 'classnames';

export class DownloadPopup extends React.Component {

  static propTypes = {
    filename:     PropTypes.string,
    filesize:     PropTypes.string,
    dateUploaded: PropTypes.string,
    downloadUrl:  PropTypes.string,
    voteUpUrl:    PropTypes.string,
    voteDownUrl:  PropTypes.string,
    voted:        PropTypes.bool,
    voteCount:    PropTypes.number,
    $button:      PropTypes.object,
    $voteWidget:  PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      opened:    false,
      voted:     props.voted || false,
      voteCount: props.voteCount || 0
    };
  }

  componentDidMount() {
    const { $button, $voteWidget } = this.props;

    $button.on('click', this.onOpen);
    $voteWidget.on('vote', this.onVote);
  }

  onOpen = (event) => {
    event.preventDefault();
    this.setState({
      opened: true
    });
  };

  onClose = (event) => {
    event.preventDefault();
    this.setState({
      opened: false
    });
  };

  onVote = (event) => {
    event.preventDefault();
    const { voted, voteCount } = this.state;
    this.setState({
      voted:     !voted,
      voteCount: voteCount + (voted ? -1 : 1)
    });
  };

  vote = (event) => {
    event.preventDefault();

    const { voteDownUrl, voteUpUrl, $voteWidget } = this.props;
    const { voted } = this.state;

    setTimeout(() => {
      $.post(voted ? voteDownUrl : voteUpUrl);
      $voteWidget.trigger('vote');
    }, 100);
  };

  render() {
    const { $button, filename, filesize, downloadUrl, dateUploaded } = this.props;
    const { opened, voted, voteCount } = this.state;

    if (!opened) {
      return null;
    }

    return (
      <div>
        <ClickOut onClickOut={this.onClose} additionalNodes={$button}>
          <div className="popup popup-file-download">
            <a className="cancel" onClick={this.onClose}>
              Cancel download <i className="fa fa-times" />
            </a>

            <div className="file-icon">
              <i className="fa fa-file" />
              <hr />

              <div className="cudos-wrapper">
                <a className={classNames('cudos', { 'with-voted': voted })} onClick={this.vote}>
                  <i className="fa fa-thumbs-up" /> {voteCount}
                </a>
              </div>
            </div>

            <h1>{filename}</h1>
            {dateUploaded
              ? <h2>Uploaded on: {dateUploaded} - {filesize}</h2>
              : <h2>{filesize}</h2>
            }

            <a href={downloadUrl} className="button" target="_blank" rel="noopener noreferrer">
              Download File
            </a>
          </div>
        </ClickOut>
        <div className="cover" />
      </div>
    );
  }
}
